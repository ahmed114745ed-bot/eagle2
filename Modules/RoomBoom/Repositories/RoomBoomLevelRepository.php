<?php

namespace Modules\RoomBoom\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\RoomBoom\Entities\RoomBoomLevel;

class RoomBoomLevelRepository
{
    public function getLatestWithRewards(int $roomId): Collection|array
    {
        return RoomBoomLevel::with(['roomBoomRewards' => function ($query) {
            $query->orderBy('priority');
        },  'roomBooms' => function ($query) use ($roomId) {
            $query->whereHas('totalRoomGift', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
                ->whereDate('started_at', Carbon::today());
        }
        ])->orderBy('level')->get();
    }
}
