<?php

namespace App\Repositories;

use App\helper\RankingHelper;
use App\Models\CoinGameUserAll;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Agency;
use App\Models\GiftLog;
use App\Models\GiftRanking;
use App\Models\CoinGameUser;
use App\Models\UserLuckyGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Achievement\Enums\AchievementType;
use App\Http\Resources\Api\V1\UsersRankingCollection;

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
        $query = CoinGameUserAll::query();
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

    private function rankerRelations(string $role): array
    {
        $relations = [
            'packs' => fn($q) => $q->select(['user_id', 'target_id', 'type'])
                ->whereIn('type', [4, 18, 10, 25])
                ->where('is_used', true)
                ->with('ware:id,name,img1,img2,show_img,color,value'),
    
            'mangerType:id,name_ar,name_en,img',
            'UserVip:id,user_id,expire,level,is_used',
            'senderLevel:id,level,type,img',
            'receiverLevel:id,level,type,img',
            'country:id,name,iso,flag',
            'profile:user_id,avatar,birthday',
    
            'medals' => fn($q) => $q->select(['achievement_level_id', 'picked', 'custom_image', 'user_id'])
                ->where('picked', true)
                ->with([
                    'achievementLevel' => fn($q) => $q->select(['id', 'valid_image'])
                        ->with('achievement:id,name,type')
                        ->whereHas('achievement', fn($q) => $q->where('type', '!=', AchievementType::ROOM_TARGET->value))
                ])
                ->limit(5),
        ];
    
        if ($role === 'roomOwner') {
            $relations['ownerRoom'] = fn($q) => $q->select(['id', 'room_name', 'owner_id']); 
        }
    
        return $relations;
    }
    

    public function getUserRanking(string $role, string $rankingType, int $perPage = 10)
    {
        return GiftRanking::query()
            ->whereHas('ranker')
            ->with([
                'ranker' => fn($q) => $q->with($this->rankerRelations($role))
            ])

            ->where('role', $role)
            ->where('ranker_type', User::class)
            ->where('type', $rankingType)
            ->orderByDesc('total_gifts')
            ->take($perPage)
            ->get();
    }

    public function getAgencyRanking(string $role, string $rankingType, int $perPage = 10)
    {
        $query = GiftRanking::query()
            ->with([
                'ranker' => function ($q) {
                    $q->with('owner')->select(['id', 'name', 'notice', 'phone', 'img', 'app_owner_id']);
                },
            ])
            ->where('role', $role)
            ->where('ranker_type', Agency::class)
            ->where('type', $rankingType)
            ->orderByDesc('total_gifts');

        return $query->paginate($perPage);
    }

    public function getUserRankingImages(string $role, string $rankingType, int $limit = 3)
    {
        $query = GiftRanking::query()
            ->with([
                'ranker' => function ($q) use ($role) {
                    $q->with([
                        'profile:user_id,avatar',
                    ])
                        ->select(['id'])
                        ->when($role === 'roomOwner', fn($q) => $q->with('ownerRoom:id,uid,room_cover'));
                },
            ])
            ->where('role', $role)
            ->where('ranker_type', User::class)
            ->where('type', $rankingType)
            ->orderByDesc('total_gifts');

        return $query->limit($limit)->get();
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
