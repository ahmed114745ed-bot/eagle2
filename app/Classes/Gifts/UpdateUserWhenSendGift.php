<?php

namespace App\Classes\Gifts;

use App\Classes\Enums\NotificationType;
use App\Exceptions\NotInfMoneyException;
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
        $diamondUser = 0;
    
            $user = User::where('id', $receivedUser->id)->lockForUpdate()->first();
    
            $diamondUser = $user->monthly_diamond_received + $totalCoins;
    
            $user->salary_is_updated = true;
            $user->total_diamond_received += $totalCoins;
    
            if ($user->type_user == 0 && $user->agency_id == 0) {
                $user->exchange_diamonds += $totalCoins;
            }
    
            $lastReceivedLevel = $user->total_received_level;
    
            try {
                (new UpgradeReceiverLevelServices())->checkUserLevelUpgrated($user);
    
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
    
            $user->save();
    
    
        try {
            uploadMonthlyDiamondReceive($receivedUser->id, $diamondUser);
    
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/monthly_diamond.log'),
            ])->info("MonthlyDiamondReceive updated for user {$receivedUser->id}: {$diamondUser}");
    
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/monthly_diamond.log'),
            ])->error("Failed to update MonthlyDiamondReceive for user {$receivedUser->id}: " . $e->getMessage());
        }
    }
    
    
    public function updateUsers(int $totalCoins, array $userIds)
    {
        // DB::table('users')->whereIn('id', $userIds)->update([
        //     'monthly_diamond_received' => DB::raw('monthly_diamond_received + ' . $totalCoins),
        //     'total_diamond_received' => DB::raw('total_diamond_received + ' . $totalCoins),
        //     'exchange_diamonds' => DB::raw("CASE WHEN agency_id = 0 THEN exchange_diamonds + $totalCoins ELSE exchange_diamonds END"),
        // ]);
        DB::transaction(function () use ($totalCoins, $userIds) {
            $users = User::
                  whereIn('id', $userIds)
                ->lockForUpdate()
                ->get();

            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'total_diamond_received'   => $user->total_diamond_received + $totalCoins,
                    'exchange_diamonds'        => $user->agency_id == 0
                        ? $user->exchange_diamonds + $totalCoins
                        : $user->exchange_diamonds,
                ]);

                $monthlyDiamond = $user->monthly_diamond_received + $totalCoins;

                uploadMonthlyDiamondReceive($user->id, $monthlyDiamond);
            }
        });
    }
    public function updateReceivedLevels(User $receivedUser)
    {
        $receivedUser->enableSaving = false;
        //update monthly diamond for received user
        // update levels
        $lastReceivedLevel = $receivedUser->total_received_level;

        $totalDiamondReceived                  = $receivedUser->total_received_diamonds;
        $levelVip                     = $this->getLevel(1, $totalDiamondReceived);
        $receivedUser->received_level = $levelVip != null ? (@$levelVip->level - $receivedUser->sub_receiver_level) ?? 0 : 0;
        if ($receivedUser->total_received_level != $lastReceivedLevel) {
            dispatch(new SendCustomOfficialMessageToUser($receivedUser->id, NotificationType::RECEIVED_LEVEL))->onQueue('notification');
        }

        //
        $receivedUser->save();
        $receivedUser->enableSaving = true;

        return $receivedUser;
    }

    public function getLevel(int $type, int $totalCoins)
    {

        return Vip::query()->where(['type' => $type])->where('exp', '<=', $totalCoins)->orderByDesc('exp')->limit(1)->first();
    }


    /*
     * 1 for receiver
     * 2, 3 for sender or vip
     */
    public function send(int $totalCoins, User $senderUser)
    {
        $senderUser->enableSaving         = false;
        $senderUser->monthly_diamond_send += $totalCoins;
        $senderUser->total_diamond_send   += $totalCoins;
        $senderUser->di                   -= $totalCoins;
        $lastSenderUser = $senderUser->total_sender_level;

        if ($senderUser->di < 0) {
            throw new NotInfMoneyException();
        }
        (new UpgradeLevelServices())->checkUserLevelUpgrated($senderUser);
        if ($senderUser->total_sender_level != $lastSenderUser) {
            dispatch(new SendCustomOfficialMessageToUser($senderUser->id, NotificationType::SENDER_LEVEL))->onQueue('notification');
        }


        $senderUser->save();
        $senderUser->enableSaving = true;

        return $senderUser;
    }

    /**
     * @throws \Throwable
     */
    public function sendFromBagAndRemoveGift(int $totalCoins, User $senderUser, int $giftId, int $number)
    {
        Log::info(['user_id' =>  $senderUser->id,'gift_id'=>$giftId]);
        $senderUser->enableSaving = false;
        $senderUser->monthly_diamond_send += $totalCoins;
        $senderUser->total_diamond_send   += $totalCoins;
        $lastSenderUser = $senderUser->total_sender_level;


        $userGift = UserGift::where('user_id', $senderUser->id)
            ->where('gift_id', $giftId)
            ->where('quantity', '>',0)
            ->where(function($query) {
                $query->where('expire', 0)
                    ->orWhereRaw('DATE_ADD(created_at, INTERVAL expire DAY) >= NOW()');
            })->first();


        throw_if(( !$userGift), \Exception::class, 'Receiver has reached maximum allowed gifts');

        $userGift->quantity -= $number;

        if ($userGift->quantity > 0) {
            $userGift->save();
        } else {
            $userGift->delete();
        }


        (new UpgradeLevelServices())->checkUserLevelUpgrated($senderUser);
        if ($senderUser->total_sender_level > $lastSenderUser) {
            dispatch(new SendCustomOfficialMessageToUser(
                $senderUser->id,
                NotificationType::SENDER_LEVEL
            ))->onQueue('notification');
        }

        $senderUser->save();
        $senderUser->enableSaving = true;

        return $senderUser;
    }


    public function getSenderLevel($totalDiamondSend, $totalDiamond, int $subSenderLevel)
    {
        $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages['exp_sender_percentage'];
        // dd($total,$totalDiamondSend,$totalDiamond ,$this->expPercentages['exp_sender_percentage']);
        $levelVip                 = $this->getLevel(2, $total);
        return $levelVip != null ? (@$levelVip->level) ?? 0 : 0;
    }

    public function getRoomLevel($total)
    {
        // $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages[0] ;
        $levelVip                 = $this->getLevel(4, $total);
        return $levelVip != null ? @$levelVip->level ?? 0 : 0;
    }

    public function getReceiverLevel($totalDiamondReceived, $totalDiamond, int $subReceiverLevel)
    {
        $total = intval($totalDiamondReceived + $totalDiamond) * $this->expPercentages['exp_received_percentage'];

        $levelVip                 = $this->getLevel(1, $total);

        return $levelVip != null ? (@$levelVip->level) ?? 0 : 0;
    }
}
