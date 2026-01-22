<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Modules\Vip\Entities\Vip;
use Modules\Vip\Entities\OVip;
use App\Models\UserGameChallange;
use Modules\Public\Jobs\RewardWinnerLevel;
use Modules\Achievement\Entities\UserAchievementLevel;

class RoomLevelServices
{
    public function update_coins_and_level(Room $room ,$amount)
    {
        if (!$room->owner) {
            return false;
        }
        if ($amount > 0) {
            $room->total_diamond +=$amount ;
            $level = Vip::where("exp","<=",$room->total_diamond)->where('type', 4)->latest()->first();
            if ($level) {
                $room->level_id = $level->id;
                $room->level = $level->level;
                $room->exp = $level->exp;
                if (count($level->gifts) > 0) {
                    foreach ($level->gifts as $gift) {
                        dispatch(new RewardWinnerLevel($user->id,$senderLevel,2))->onQueue('level_rewards');
                    }
                }
            }

            $room->save();
        }
    }
}
