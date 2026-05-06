<?php

namespace App\Classes\Gifts;

use App\Classes\Enums\NotificationType;
use App\Exceptions\NotInfMoneyException;
use App\Jobs\IncreaseDiamondJob;
use App\Jobs\SendCustomOfficialMessageToUser;
use App\Models\User;
use Mockery\Exception;
use Modules\Vip\Entities\Vip;
use App\Models\UserGift;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Modules\Public\Http\Services\UpgradeReceiverLevelServices;

class UpdateUserWhenSendGift
{

    private array $expPercentages;

    public function __construct()
    {
        $this->expPercentages = Config::get('exp_percentages') ?? [1, 1];
    }

    public function update(int $totalCoins, User $receivedUser)
    {
        DB::transaction(function () use ($totalCoins, $receivedUser) {
            $user = User::where('id', $receivedUser->id)->lockForUpdate()->first();

            if (!$user) {
                throw new \Exception("User not found");
            }

            $updateData = [
                'salary_is_updated' => DB::raw('GREATEST(salary_is_updated, 1)'),
                'total_diamond_received' => DB::raw("total_diamond_received + {$totalCoins}"),
            ];

            if ($user->type_user == 0 && $user->agency_id == 0) {
                $updateData['exchange_diamonds'] = DB::raw("exchange_diamonds + {$totalCoins}");
            }

            DB::table('users')->where('id', $user->id)->update($updateData);

            $user->refresh();

            $lastReceivedLevel = $user->total_received_level;

            try {
                (new UpgradeReceiverLevelServices())->checkUserLevelUpgrated($user);
                $user->save();

                if ($user->total_received_level != $lastReceivedLevel) {
                    dispatch(new SendCustomOfficialMessageToUser($user->id, NotificationType::RECEIVED_LEVEL))
                        ->onQueue('notification');
                }
            } catch (\Exception $e) {
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/diamond_upgrade.log'),
                ])->error("Error in checkUserLevelUpgrated for user {$user->id}: " . $e->getMessage());
            }

            IncreaseDiamondJob::dispatch($user->id, $totalCoins)
                ->afterCommit()
                ->onQueue('increment-diamond');
        });
    }

    public function updateUsers(int $totalCoins, array $userIds)
    {
        sort($userIds);

        $affectedUsers = DB::transaction(function () use ($totalCoins, $userIds) {
            $users = User::whereIn('id', $userIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $updatedUsers = [];

            foreach ($users as $user) {
                $updateData = [
                    'salary_is_updated' => DB::raw('GREATEST(salary_is_updated, 1)'),
                    'total_diamond_received' => DB::raw("total_diamond_received + {$totalCoins}"),
                ];

                if ($user->agency_id == 0) {
                    $updateData['exchange_diamonds'] = DB::raw("exchange_diamonds + {$totalCoins}");
                }

                DB::table('users')->where('id', $user->id)->update($updateData);

                $updatedUsers[] = [
                    'id' => $user->id,
                    'last_received_level' => $user->total_received_level,
                    'monthly_diamond_received' => $user->monthly_diamond_received,
                ];
            }

            return $updatedUsers;
        }, 5);

        if (empty($affectedUsers)) {
            return;
        }

        $usersById = User::whereIn('id', array_column($affectedUsers, 'id'))
            ->get()
            ->keyBy('id');

        foreach ($affectedUsers as $userMeta) {
            $user = $usersById->get($userMeta['id']);
            if (!$user) {
                continue;
            }

            $lastReceivedLevel = $userMeta['last_received_level'];
            try {
                (new UpgradeReceiverLevelServices())->checkUserLevelUpgrated($user);
                $user->save();

                if ($user->total_received_level != $lastReceivedLevel) {
                    dispatch(new SendCustomOfficialMessageToUser($user->id, NotificationType::RECEIVED_LEVEL))
                        ->onQueue('notification');
                }
            } catch (\Exception $e) {
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/diamond_upgrade.log'),
                ])->error("Error in checkUserLevelUpgrated for user {$user->id}: " . $e->getMessage());
            }

            incrementMonthlyDiamond(
                $user->id,
                $totalCoins  
            );
        }
    }
    public function updateReceivedLevels(User $receivedUser)
    {
        return DB::transaction(function () use ($receivedUser) {
            $user = User::where('id', $receivedUser->id)->lockForUpdate()->first();

            if (!$user) {
                throw new \Exception("User not found");
            }

            $lastReceivedLevel = $user->total_received_level;
            $totalDiamondReceived = $user->total_received_diamonds;
            $levelVip = $this->getLevel(1, $totalDiamondReceived);
            $receivedLevel = $levelVip != null ? (@$levelVip->level - $user->sub_receiver_level) ?? 0 : 0;

            DB::table('users')->where('id', $user->id)->update([
                'received_level' => $receivedLevel,
            ]);

            $user->refresh();

            if ($user->total_received_level != $lastReceivedLevel) {
                dispatch(new SendCustomOfficialMessageToUser($user->id, NotificationType::RECEIVED_LEVEL))
                    ->onQueue('notification');
            }

            return $user;
        });
    }

    public function getLevel(int $type, int $totalCoins)
    {

        return Vip::query()->where(['type' => $type])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->orderByDesc('level')->limit(1)->first();
    }


    /*
     * 1 for receiver
     * 2, 3 for sender or vip
     */
    public function send(int $totalCoins, User $senderUser)
    {

        $affected = User::where('id', $senderUser->id)
            ->where('di', '>=', $totalCoins)
            ->update([
                'di' => DB::raw("di - {$totalCoins}"),
                'monthly_diamond_send' => DB::raw("monthly_diamond_send + {$totalCoins}"),
                'total_diamond_send' => DB::raw("total_diamond_send + {$totalCoins}")
            ]);

        if ($affected === 0) {
            throw new NotInfMoneyException();
        }

        // Re-fetch latest user state after raw DB update
        $senderUser->refresh();

        $lastLevel = $senderUser->total_sender_level;

        (new UpgradeLevelServices())->checkUserLevelUpgrated($senderUser);

        if ($senderUser->total_sender_level != $lastLevel) {
            dispatch(
                new SendCustomOfficialMessageToUser(
                    $senderUser->id,
                    NotificationType::SENDER_LEVEL
                )
            )->onQueue('notification');
        }


        return $senderUser;
    }
    public function sendFromBagAndRemoveGift(int $totalCoins, User $senderUser, int $giftId, int $number)
    {
        return DB::transaction(function () use ($totalCoins, $senderUser, $giftId, $number) {
            $user = User::where('id', $senderUser->id)->lockForUpdate()->first();

            if (!$user) {
                throw new \Exception("User not found");
            }

            $userGift = UserGift::where('user_id', $user->id)
                ->where('gift_id', $giftId)
                ->where('quantity', '>', 0)
                ->where(function ($query) {
                    $query->where('expire', 0)
                        ->orWhereRaw('DATE_ADD(created_at, INTERVAL expire DAY) >= NOW()');
                })
                ->lockForUpdate()
                ->first();

            throw_if((!$userGift), \Exception::class, 'Receiver has reached maximum allowed gifts');

            $updateData = [
                'monthly_diamond_send' => DB::raw("monthly_diamond_send + {$totalCoins}"),
                'total_diamond_send' => DB::raw("total_diamond_send + {$totalCoins}"),
            ];

            DB::table('users')->where('id', $user->id)->update($updateData);

            $userGift->quantity -= $number;

            if ($userGift->quantity > 0) {
                $userGift->save();
            } else {
                $userGift->delete();
            }

            $user->refresh();

            $lastSenderUser = $user->total_sender_level;

            (new UpgradeLevelServices())->checkUserLevelUpgrated($user);

            if ($user->total_sender_level > $lastSenderUser) {
                dispatch(new SendCustomOfficialMessageToUser(
                    $user->id,
                    NotificationType::SENDER_LEVEL
                ))->onQueue('notification');
            }

            return $user;
        });
    }


    public function getSenderLevel($totalDiamondSend, $totalDiamond, int $subSenderLevel)
    {
        $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages['exp_sender_percentage'];
        // dd($total,$totalDiamondSend,$totalDiamond ,$this->expPercentages['exp_sender_percentage']);
        $levelVip = $this->getLevel(2, $total);
        return $levelVip != null ? (@$levelVip->level) ?? 0 : 0;
    }

    public function getRoomLevel($total)
    {
        // $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages[0] ;
        $levelVip = $this->getLevel(4, $total);
        return $levelVip != null ? @$levelVip->level ?? 0 : 0;
    }

    public function getRoomLevelDetails($total)
    {
        $levelVip = $this->getLevel(4, $total);
        return $levelVip;
    }

    public function getReceiverLevel($totalDiamondReceived, $totalDiamond, int $subReceiverLevel)
    {
        $total = intval($totalDiamondReceived + $totalDiamond) * $this->expPercentages['exp_received_percentage'];

        $levelVip = $this->getLevel(1, $total);

        return $levelVip != null ? (@$levelVip->level) ?? 0 : 0;
    }
}
