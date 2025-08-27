<?php

namespace Modules\RoomBoom\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\RoomBoom\Entities\RoomBoomLevel;

class RoomBoomLevelRepository
{
    public function getLatestWithRewards(int $roomId): Collection|array
    {
        $tz = getTimezone();
        $today = Carbon::today($tz)->setTimezone('UTC');

        info($tz);
        info($today);
        return RoomBoomLevel::with(['roomBoomRewards' => function ($query) {
            $query->orderBy('priority');
        },  'roomBooms' => function ($query) use ($roomId, $today) {
            $query->whereHas('totalRoomGift', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
                ->whereDate('started_at', $today);
        }
        ])->orderBy('level')->get();
    }

    public function getVideos(): \Illuminate\Support\Collection
    {
        return RoomBoomLevel::select(['id', 'level', 'video'])->orderBy('level')->get();
    }
}
