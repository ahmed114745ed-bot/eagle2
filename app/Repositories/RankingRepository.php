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

    public function getGiftLogs($class, $rel, $type, $limit,$keywords)
    {
        $query = GiftLog::query()->whereHas($rel)
            ->when($class == 3, fn($q) => $q->with('roomOwner.ownerRoom:id,uid,room_name,room_cover'))
            ->when($class != 3, fn($q) => $q->with($rel));

        $this->applyDateFilters($query, $type);

        return $query->selectRaw("sum(giftPrice) as exp, $keywords")
                    ->groupBy($keywords)->orderByRaw("exp desc")
                    ->limit($limit)->get()->reject(function ($q) {
                        return $q->exp == 0;
                    });
    }

    protected function applyDateFilters(&$query, $type)
    {
        if ($type == 0) {
            $query->whereBetween('created_at', [Carbon::now()->startOfHour(), Carbon::now()->endOfHour()]);
        } elseif ($type == 1) {
            $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($type == 2) {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($type == 3) {
            $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
        }
    }
}