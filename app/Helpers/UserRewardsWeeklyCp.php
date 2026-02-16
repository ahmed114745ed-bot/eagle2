<?php

namespace App\Helpers;

use App\Support\PackageHelper;
use Carbon\Carbon;
use Utd\Vip\Entities\OVip;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\UserCommon;
use Utd\Achievements\Entities\UserAchievementLevel;



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
        $vip = PackageHelper::isInstalled('vip') ? OVip::find($vipId) : null;
        UserCommon::addVipToUser($userOne, $vip, $expire, null, 'weekly-cp');
        UserCommon::addVipToUser($userTwo, $vip, $expire, null, 'weekly-cp');
    }

    public static function assignBadge($badgeId, $expire, $userOne, $userTwo)
    {
        Common::userBadge($userOne, $badgeId, $expire, 'weekly-cp');
        Common::userBadge($userTwo, $badgeId, $expire, 'weekly-cp');
    }

    public static  function assignWare($ware, $reward, $userOne, $userTwo)
    {
        // Assign the ware based on gender requirements
        self::genderForReward($ware, $reward, $userOne, $userTwo);
    }

    public static function genderForReward($ware, $reward, $userOne, $userTwo)
    {
        if ($reward->gender === 'male') {
            self::assignWareToUserByGender($ware, $reward->expire, $userOne, 1);
            self::assignWareToUserByGender($ware, $reward->expire, $userTwo, 1);
        } elseif ($reward->gender === 'female') {
            self::assignWareToUserByGender($ware, $reward->expire, $userOne, 2);
            self::assignWareToUserByGender($ware, $reward->expire, $userTwo, 2);
        } else {
            UserCommon::addEvintsWareToUser($userOne, $ware, $reward->expire, null, 'weekly-cp');
            UserCommon::addEvintsWareToUser($userTwo, $ware, $reward->expire, null, 'weekly-cp');
        }
    }

    public  static function assignWareToUserByGender($ware, $expire, $user, $gender)
    {
        if ($user->profile?->gender === $gender) {
            UserCommon::addEvintsWareToUser($user, $ware, $expire, null, 'weekly-cp');
        }
    }
    public static function assignAchievement($itemId, $expire, $userOne, $userTwo)
    {
        $dateTimestamp = Carbon::parse($expire)->format('Y-m-d H:i:s');

        $attributes = [
            'custom_image' => $itemId,
            'end_at'       => $dateTimestamp,
        ];

        if (class_exists(UserAchievementLevel::class)) {
            UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userOne->id]));
            UserAchievementLevel::create(array_merge($attributes, ['user_id' => $userTwo->id]));
        }
    }
}
