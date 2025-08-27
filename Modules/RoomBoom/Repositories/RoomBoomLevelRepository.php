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
        $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');

        info($tz);
        info($todayStart);
        return RoomBoomLevel::with(['roomBoomRewards' => function ($query) {
            $query->orderBy('priority');
        },  'roomBooms' => function ($query) use ($roomId, $todayStart) {
            $query->whereHas('totalRoomGift', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
                ->whereDate('started_at', $todayStart);
        }
        ])->orderBy('level')->get();
    }

    public function getVideos(): \Illuminate\Support\Collection
    {
        return RoomBoomLevel::select(['id', 'level', 'video'])->orderBy('level')->get();
    }
}
