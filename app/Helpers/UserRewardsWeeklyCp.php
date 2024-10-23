<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\UserCommon;
use Modules\Achievement\Entities\UserAchievementLevel;



class UserRewardsWeeklyCp
{
    public static function getUserById($id)
    {
        return User::find($id);
    }

    public  function distributeRewards($rewards, $userOne, $userTwo)
    {
        foreach ($rewards as $reward) {
            switch ($reward->type) {
                case 'coins':
                    $this->assignCoins($reward->item_id, $userOne, $userTwo);
                    break;
                case 'vip':
                    $this->assignVip($reward->item_id, $reward->expire, $userOne, $userTwo);
                    break;
                case 'ware':
                    $ware = Ware::find($reward->item_id);
                    $this->assignWare($ware, $reward, $userOne, $userTwo);
                    break;
                case 'achievement':
                    $this->assignAchievement($reward->item_id, $reward->expire, $userOne, $userTwo);
                    break;
            }
        }
    }

    public static function assignCoins($amount, $userOne, $userTwo)
    {
        $userOne->increment('di', $amount);
        $userTwo->increment('di', $amount);
    }

    public static function assignVip($vipId, $expire, $userOne, $userTwo)
    {
        $vip = OVip::find($vipId);
        UserCommon::addVipToUser($userOne, $vip, $expire);
        UserCommon::addVipToUser($userTwo, $vip, $expire);
    }

    public static  function assignWare($ware, $reward, $userOne, $userTwo)
    {
        // Assign the ware based on gender requirements
        self::genderForReward($ware, $reward, $userOne, $userTwo);
    }

    public  function genderForReward($ware, $reward, $userOne, $userTwo)
    {
        if ($reward->gender === 'male') {
            $this->assignWareToUserByGender($ware, $reward->expire, $userOne, 1);
            $this->assignWareToUserByGender($ware, $reward->expire, $userTwo, 1);
        } elseif ($reward->gender === 'female') {
            $this->assignWareToUserByGender($ware, $reward->expire, $userOne, 2);
            $this->assignWareToUserByGender($ware, $reward->expire, $userTwo, 2);
        } else {
            UserCommon::addWareToUser($userOne, $ware, $reward->expire);
            UserCommon::addWareToUser($userTwo, $ware, $reward->expire);
        }
    }

    public  static function assignWareToUserByGender($ware, $expire, $user, $gender)
    {
        if ($user->profile?->gender === $gender) {
            UserCommon::addWareToUser($user, $ware, $expire);
        }
    }
    public static function assignAchievement($itemId, $expire, $userOne, $userTwo)
    {
        $dateTimestamp = Carbon::parse($expire)->format('Y-m-d H:i:s');

        $attributes = [
            'custom_image' => $itemId,
            'end_at'       => $dateTimestamp,
        ];

        UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userOne->id]));
        UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userTwo->id]));
    }
}
