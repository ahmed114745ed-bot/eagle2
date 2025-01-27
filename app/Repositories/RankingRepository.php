<?php

namespace App\Repositories;

use App\Models\GiftLog;
use App\Models\UserLuckyGift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RankingRepository
{
    public function getUserLuckyGifts($type, $limit)
    {
        $query = UserLuckyGift::query();
        $this->applyDateFilters($query, $type);

        return $query->selectRaw("sum(total_win) as exp, user_id")
            ->groupBy('user_id')
            ->orderByRaw("exp desc")
            ->limit($limit)
            ->get()
            ->reject(function ($q) {
                return $q->exp == 0;
            });
    }

    public function getGiftLogs($class, $rel, $type, $limit, $keywords)
    {
        $query = GiftLog::query();
        if ($class != 5) {
            $query = $query->whereHas($rel)
                ->when($class == 3, fn($q) => $q->with('roomOwner.ownerRoom:id,uid,room_name,room_cover'))
                ->when($class != 3, fn($q) => $q->with($rel));
        } else {
            $query = $query->with($rel)->whereHas('agency');
        }
        $this->applyDateFilters($query, $type);

        return $query->selectRaw("sum(giftPrice) as exp, $keywords")
            ->groupBy($keywords)->orderByRaw("exp desc")
            ->limit($limit)->get()->reject(function ($q) {
                return $q->exp == 0;
            });
    }

    public function getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords)
    {
        $query = GiftLog::query()->where('roomowner_id', $room_id)->whereHas($rel)

            ->when($class != 3, fn($q) => $q->with($rel));

        $this->applyDateFilters($query, $type);

        return $query->selectRaw("sum(giftPrice) as exp, $keywords")
            ->groupBy($keywords)->orderByRaw("exp desc")
            ->limit($limit)->get()->reject(function ($q) {
                return $q->exp == 0;
            });
    }

    public function getGiftLogsUserForRoomOwnerId($class, $rel, $type, $userId, $room_id, $keywords)
    {
        $query = GiftLog::query()->where('roomowner_id', $room_id)->whereHas($rel)

            ->when($class != 3, fn($q) => $q->with($rel));

        $this->applyDateFilters($query, $type);

        return $query->selectRaw("sum(giftPrice) as exp, $keywords")->where($keywords, $userId)->groupBy($keywords)->first();
    }

    protected function applyDateFilters(&$query, $type)
    {
       
        // if ($type == 0) {
        //     $query->whereBetween('created_at', [Carbon::now()->startOfHour(), Carbon::now()->endOfHour()]);
        // } elseif ($type == 1) {
        //     $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        // } elseif ($type == 2) {
        //     $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        // } elseif ($type == 3) {
        //     $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        // }
        $currentTime = now()->subHours(6);

        if ($type == 0) {

            $startHour = $currentTime->startOfHour();
            $endHour = $currentTime->endOfHour();
            $query->whereBetween('created_at', [$startHour, $endHour]);

        } elseif ($type == 1) {

            $startDay = $currentTime->copy()->subDay()->setTime(18, 0, 0);
            $endDay = $currentTime->copy()->setTime(17, 59, 59);
            $query->whereBetween('created_at', [$startDay, $endDay]);


        } elseif ($type == 2) {

            $startWeek = $currentTime->copy()->startOfWeek()->subDay()->setTime(18, 0, 0); 
            $endWeek = $currentTime->copy()->endOfWeek()->setTime(17, 59, 59); 
            $query->whereBetween('created_at', [$startWeek, $endWeek]);

        } elseif ($type == 3) {

            $query->whereMonth('created_at', $currentTime->month)->whereYear('created_at', $currentTime->year);
        }
    }
}
