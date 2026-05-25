<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Agency;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\helper\TimeHelper;
use App\Models\GiftRanking;
use App\Models\CoinGameUser;
use App\helper\RankingHelper;
use App\Models\CoinGameUserAll;
use App\Models\CoinGameUserMerged;
use Illuminate\Support\Facades\DB;
use App\Models\CoinGameUserArchive;
use Illuminate\Database\Eloquent\Model;
use App\Models\CoinGameUserMergedMonthly;
use Modules\Achievement\Enums\AchievementType;
use App\Http\Resources\Api\V1\UsersRankingCollection;
use Modules\LuckyBox\Entities\UserLuckyGift;
use Illuminate\Support\Facades\Cache;

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

    public function getUserGameCoins(int $type, int $limit)
    {
        [$from, $to] = $this->getDateRange($type);

        switch ($type) {
            case 0:
                $model = CoinGameUser::class;
                break;

            case 1:
            case 2:
                $model = CoinGameUserMerged::class;

                break;

            case 3:
                $model = CoinGameUserMergedMonthly::class;
                break;

            default:
                $model = CoinGameUserMerged::class;
                break;
        }

        return $this->getCoinsForModel($model, $from, $to, $limit);
    }


    protected function getCoinsForModel(string $model, $from, $to, int $limit)
    {
        return $model::query()
            ->select('user_id', DB::raw("SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as exp"))
            ->whereBetween('date', [$from, $to])
            ->whereHas('user')
            ->with($this->userRelations())
            ->groupBy('user_id')
            ->orderByDesc('exp')
            ->limit($limit)
            ->get();
    }


    protected function userRelations(): array
    {
        return [
            'user:id,name,email,country_id',
            'user.country:id,name,iso,flag',
            'user.profile:user_id,avatar,birthday',
            'user.mangerType:id,name_ar,name_en,img',
            'user.UserVip:id,user_id,expire,level,is_used',
        ];
    }


    protected function getDateRange(int $type): array
    {
        $timezone = Common::timeZone();
        switch ($type) {
            case 0:
                $from = Carbon::now($timezone)->startOfHour();
                $to   = Carbon::now($timezone)->endOfHour();
                break;

            case 1:
                $from = Carbon::now($timezone)->startOfDay();
                $to   = Carbon::now($timezone)->endOfDay();
                break;

            case 2:
                $from = Carbon::now($timezone)->startOfWeek();
                $to   = Carbon::now($timezone)->endOfWeek();
                break;

            case 3:
                $from = Carbon::now($timezone)->startOfMonth();
                $to   = Carbon::now($timezone)->endOfMonth();
                break;

            default:
                $from = Carbon::now($timezone)->startOfDay();
                $to   = Carbon::now($timezone)->endOfDay();
                break;
        }

        return [$from, $to];
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
        $cacheKey = "gift_rankings_{$class}_{$rel}_{$type}_{$limit}_{$keywords}";

        return Cache::remember($cacheKey, 300, function () use ($class, $rel, $type, $limit, $keywords) {
            $query = GiftLog::query()->whereHas($rel)
                ->when($class == 3, fn($q) => $q->with('roomOwner.ownerRoom:id,uid,room_name,room_cover'))
                ->when($class != 3, fn($q) => $q->with($rel));

            $this->applyDateFilters($query, $type);

            return $query->selectRaw("sum(giftPrice) as exp, $keywords")
                ->groupBy($keywords)->orderByRaw("exp desc")
                ->limit($limit)->get()->reject(function ($q) {
                    return $q->exp == 0;
                });
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



        return $relations;
    }


    public function getUserRanking(string $role, string $rankingType, int $perPage = 10, $type = 1)
    {
        $query = GiftRanking::query();
        $this->applyDateFiltersV2($query, $type);

        return $query->whereHas('ranker')
            ->where('role', $role)
            ->when($role == 'roomId', function ($query) {
                return $query->where('ranker_type', Room::class)->with([
                    'ranker' => function ($q) {
                        $q->with('owner')->select(['id', 'room_name', 'uid', 'room_cover']);
                    },
                ]);
            })
            ->when($role != 'roomId', function ($query) use ($role) {
                return $query->where('ranker_type', User::class)->with([
                    'ranker' => fn($q) => $q->with($this->rankerRelations($role))
                        ->when($role === 'roomOwner', fn($q) => $q->with('ownerRoom:id,uid,room_cover,room_name'))
                ]);
            })
            ->when($role === 'roomOwner', function ($query) {
                return $query->whereExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('rooms')
                        ->whereColumn('rooms.uid', 'gift_rankings.ranker_id')
                        ->where('rooms.room_status', '!=', '4');
                });
            })
            ->where('type', $rankingType)
            ->orderByDesc('total_gifts')
            ->take($perPage)
            ->get();
    }

    public function getAgencyRanking(string $role, string $rankingType, int $perPage = 10, $type  = 1)
    {
        $query = GiftRanking::query();
        $this->applyDateFiltersV2($query, $type);
        $query->with([
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
        $query = GiftRanking::query()->whereHas('ranker')
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
        $this->applyDateFiltersV2($query, 1);

        return $query->limit($limit)->get();
    }

    public function getGiftLogsV2($class, $rel, $type, $limit, $keywords)
    {
        $cacheKey = "gift_rankings_v2_{$class}_{$rel}_{$type}_{$limit}_{$keywords}";

        return Cache::remember($cacheKey, 300, function () use ($class, $rel, $type, $limit, $keywords) {
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
        $timezone = Common::timeZone();
        $now = Carbon::now($timezone);



        [$start, $end] = match ($type) {
            0 => [$now->copy()->startOfHour(), $now->copy()->endOfHour()],
            1 => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            2 => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            3 => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [null, null],
        };

        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }
    }



    protected function applyDateFiltersV2(&$query, $type)
    {
        $timezone = Common::timeZone();
        $startWeek = Common::getSettingValue('week_start');
        $endWeek = Common::getSettingValue('week_end');
        if ($type == 0) {
            $query->whereBetween('created_at', [Carbon::now($timezone)->startOfHour(), Carbon::now($timezone)->endOfHour()]);
        } elseif ($type == 1) {
            $query->whereBetween('created_at', [Carbon::now($timezone)->startOfDay(), Carbon::now($timezone)->endOfDay()]);
        } elseif ($type == 2) {
            $query->whereBetween('created_at', [Carbon::now($timezone)->startOfWeek(), Carbon::now($timezone)->endOfWeek()]);
        } elseif ($type == 3) {
            $query->whereMonth('created_at', Carbon::now($timezone)->month)->whereYear('created_at', Carbon::now($timezone)->year);
        }
    }
}
