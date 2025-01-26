<?php

namespace App\Classes\Gifts;

use App\Classes\Enums\NotificationType;
use App\Exceptions\NotInfMoneyException;
use App\Helpers\Common;
use App\Jobs\SendCustomOfficialMessageToUser;
use App\Models\User;
use App\Models\Vip;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Modules\Public\Http\Services\UpgradeReceiverLevelServices;

class UpdateUserWhenSendGift
{

    private array $expPercentages;

    public function __construct() {
        $this->expPercentages = Config::get('exp_percentages') ?? [1,1];

    }

    public function update(int $totalCoins, User $receivedUser)
    {


        $receivedUser->enableSaving = false;

        // add is salary updated
        $receivedUser->salary_is_updated = true;
        //update monthly diamond for received user
        $receivedUser->monthly_diamond_received += $totalCoins;
        $receivedUser->total_diamond_received   += $totalCoins;
        // update levels
        if ($receivedUser->user_type == 0 && $receivedUser->agency_id == 0) {
            $receivedUser->exchange_diamonds += $totalCoins;
        }

        $lastReceivedLevel = $receivedUser->total_received_level;

        (new UpgradeReceiverLevelServices())->checkUserLevelUpgrated($receivedUser);
        if ($receivedUser->total_received_level > $lastReceivedLevel) {
            dispatch(new SendCustomOfficialMessageToUser($receivedUser->id, NotificationType::RECEIVED_LEVEL))->onQueue('notification');
        }
        $receivedUser->save();
        $receivedUser->enableSaving = true;


        return $receivedUser;

    }
    public function updateUsers(int $totalCoins, array $userIds)
    {
        DB::table('users')->whereIn('id', $userIds)->update([
                                                                'monthly_diamond_received' => DB::raw('monthly_diamond_received + ' . $totalCoins),
                                                                'total_diamond_received' => DB::raw('total_diamond_received + ' . $totalCoins),
                                                                'exchange_diamonds' => DB::raw("CASE WHEN agency_id = 0 THEN exchange_diamonds + $totalCoins ELSE exchange_diamonds END"),
                                                            ]);


    }
    public function updateReceivedLevels( User $receivedUser)
    {
        $receivedUser->enableSaving = false;
        //update monthly diamond for received user
        // update levels
        $lastReceivedLevel = $receivedUser->total_received_level;

        $totalDiamondReceived                  = $receivedUser->total_received_diamonds;
        $levelVip                     = $this->getLevel(1, $totalDiamondReceived);
        $receivedUser->received_level = $levelVip != null ? (@$levelVip->level - $receivedUser->sub_receiver_level) ?? 0: 0;
        if ($receivedUser->total_received_level > $lastReceivedLevel) {
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
        if ($senderUser->total_sender_level > $lastSenderUser) {
            dispatch(new SendCustomOfficialMessageToUser($senderUser->id, NotificationType::SENDER_LEVEL))->onQueue('notification');
        }


        $senderUser->save();
        $senderUser->enableSaving = true;

        return $senderUser;

    }

    public function getSenderLevel($totalDiamondSend, $totalDiamond, int $subSenderLevel)
    {
        $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages[0] ;
        $levelVip                 = $this->getLevel(2, $total);
        return $levelVip != null ? (@$levelVip->level - $subSenderLevel) ?? 0 : 0;
    }

    public function getRoomLevel($total)
    {
        // $total = intval($totalDiamondSend + $totalDiamond) * $this->expPercentages[0] ;
        $levelVip                 = $this->getLevel(4, $total);
        return $levelVip != null ? @$levelVip->level ?? 0 : 0;
    }

    public function getReceiverLevel($totalDiamondReceived, $totalDiamond, int $subSenderLevel)
    {
        $total = intval($totalDiamondReceived + $totalDiamond) * $this->expPercentages[1] ;
        $levelVip                 = $this->getLevel(1, $total);
        return $levelVip != null ? (@$levelVip->level - $subSenderLevel) ?? 0 : 0;
    }


}
