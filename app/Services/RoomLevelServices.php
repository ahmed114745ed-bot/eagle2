<?php

namespace App\Services;

use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Support\PackageHelper;
use Utd\Vip\Entities\OVip;
use App\Models\User;
use App\Models\UserGameChallange;
use Utd\Room\Entities\Room;
use Utd\Vip\Entities\Vip;
use App\Models\Ware;
use Utd\Achievements\Entities\UserAchievementLevel;

class RoomLevelServices
{
    public function update_coins_and_level(Room $room ,$amount)
    {
        if (!$room->owner) {
            return false;
        }
        if ($amount > 0) {
            $room->total_diamond +=$amount ;
            $level = PackageHelper::isInstalled('vip')
                ? Vip::where("exp","<=",$room->total_diamond)->where('type', 4)->latest()->first()
                : null;
            if ($level) {
                $room->level_id = $level->id;
                $room->level = $level->level;
                $room->exp = $level->exp;
                if (count($level->gifts) > 0) {
                    foreach ($level->gifts as $gift) {
                        dispatch(new RewardWinnerLeve($user->id,$senderLevel,2))->onQueue('level_rewards');
                    }
                }
            }

            $room->save();
        }
    }
}
