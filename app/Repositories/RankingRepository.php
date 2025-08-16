<?php

namespace App\Repositories;

use App\Models\GiftRanking;
use App\Models\User;
use Carbon\Carbon;
use App\Models\GiftLog;
use App\Models\CoinGameUser;
use App\Models\UserLuckyGift;
use Illuminate\Support\Facades\DB;
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

    public function getUserGameCoins($type, $limit)
    {
        $query = CoinGameUser::query();
        $this->applyDateFilters($query, $type);

        return   $query->select(
            'user_id',
            DB::raw(" SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS exp")
        )->groupBy('user_id')->with('user')->orderByRaw("exp desc")->limit($limit)->get();
    }

    public function getUserGameCoinsV2($type, $limit)
    {
        $query = CoinGameUser::query();
        $this->applyDateFiltersV2($query, $type);

        return   $query->select(
            'user_id',
            DB::raw(" SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS exp")
        )->groupBy('user_id')->with('user')->orderByRaw("exp desc")->limit($limit)->get();
    }

    public function getGiftLogs($class, $rel, $type, $limit, $keywords)
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

    public function getUserRanking(string $role, string $rankingType, int $perPage)
    {
        $query = GiftRanking::query()
            ->with([
                'ranker' => function ($q) {
                    $q->with([
                        'packs.ware:id,name',
                        'mangerType:id,name',
                        'UserVip:id,user_id,expire,level,is_used',
                        'senderLevel:id,level,type,img',
                        'receiverLevel:id,level,type,img',
                        'country:id,name,iso,flag',
                        'profile:user_id,avatar,birthday',
                    ]);
                },
                'medals.achievementLevel.achievement:id,name,description',
            ])
            ->when($role === 'roomOwner', fn($q) => $q->with('ownerRoom:id,owner_id,name'))
            ->where('role', $role)
            ->where('ranker_type', User::class)
            ->where('type', $rankingType);

        return $query->paginate($perPage);
    }

    public function getGiftLogsV2($class, $rel, $type, $limit, $keywords)
    {
        $query = GiftLog::query()->whereHas($rel)
            ->when($class == 3, fn($q) => $q->with('roomOwner.ownerRoom:id,uid,room_name,room_cover'))
            ->when($class != 3, fn($q) => $q->with($rel));

        $this->applyDateFiltersV2($query, $type);

        return $query->selectRaw("SUM(giftPrice) as exp, $keywords")
            ->groupBy($keywords)
            ->havingRaw('SUM(giftPrice) > 0')
            ->orderByDesc('exp')
            ->limit($limit)
            ->get();
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

    protected function applyDateFiltersV2(&$query, $type)
    {
        if ($type == 0) {
            $query->whereBetween('created_at', [Carbon::now()->startOfHour(), Carbon::now()->endOfHour()]);
        } elseif ($type == 1) {
            $query->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($type == 2) {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($type == 3) {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }
    }
}
