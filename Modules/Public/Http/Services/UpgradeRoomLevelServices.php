<?php

namespace Modules\Public\Http\Services;

use App\Models\Vip;
use App\Models\OVip;
use App\Models\User;
use App\Models\Ware;
use App\Models\Banner;
use App\Models\Config;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use App\Models\EarnedDiamond;
use Illuminate\Support\Facades\DB;
use Modules\Public\Entities\levelInterval;
use Modules\Public\Jobs\RewardWinnerLevel;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Models\Room;
use Modules\Public\Entities\RewardLevelInterval;
use Modules\Public\Entities\WinnerLevelInterval;
use Modules\Achievement\Entities\UserAchievementLevel;


class UpgradeRoomLevelServices
{
    public function sendGigt(Room &$room, $diamonds = null)
    {
        $user = $room->owner;
        $this->addDiamond($room, $diamonds);
    }

    public function  addDiamond(Room &$room, $diamonds)
    {
        $room->total_diamond += $diamonds ?? 0;
        $this->checkUserLevelUpgrated($room);
        $room->save();
    }
 

    public function checkUserLevelUpgrated(Room &$room)
    {
        $user = $room->owner;
        $oldroomLevel = $room->level_id;

        $roomLevel = (new UpdateUserWhenSendGift())->getRoomLevel($room->total_diamond);
        if ($roomLevel > $oldroomLevel) {
            $room->level_id = $roomLevel;
            $hadNotRewards = $this->hadNotRewards($user->id, $roomLevel);
            if ($hadNotRewards) {
                dispatch(new RewardWinnerLevel($user->id,$roomLevel,3))->onQueue('level_rewards');
            }
        }
    }

    private function hadNotRewards(int $userId, int $roomLevel)
    {
        return !WinnerLevelInterval::query()->whereHas('levelInterval',function ($query)  {
            $query->where('type', 3);
        })->where('min', '<=', $roomLevel)->where('max', '>=', $roomLevel)->where('user_id', $userId)->exists();
    }


}
