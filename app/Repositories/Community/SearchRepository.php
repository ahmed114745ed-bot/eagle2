<?php

namespace App\Repositories\Community;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\CommunityResource;
use App\Models\BlackList;
use App\Models\OfficialMessage;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchRepository implements SearchRepositoryInterface
{
    public function saveSearchHistory(int $userId, string $keywords): void
    {
        $searchExists = DB::table('search_histories')
            ->where('user_id', $userId)
            ->where('search', $keywords)
            ->where('type', 2)
            ->exists();

        if (!$searchExists) {
            DB::table('search_histories')->insert([
                'search' => $keywords,
                'user_id' => $userId,
            ]);
        }
    }

    public function searchRooms(int $userId, string $keywords, int $page = 1): array|Collection
    {
        // $user = User::searchByUuid($keywords)->first();
        $user = Auth::user();
        $blockedByMe = $user->blockedUsers()->pluck('from_uid')->toArray();
        $blockedMe = $user->blockedMe()->pluck('user_id')->toArray();

        $blockedUserIds = array_unique(array_merge($blockedByMe, $blockedMe));

        // $user = User::likeSearchByUuid($keywords)
        //     ->with(['packs' => function ($q) {
        //         $q->where('type', 16)
        //             ->where('is_used', 1)
        //             ->where(function ($q) {
        //                 $q->where('expire', 0)
        //                 ->orWhere('expire', '>=', now()->timestamp);
        //             });
        //     }])->first();
        $user = User::query()
            ->where(function ($q) use ($keywords) {
                $q->where('uuid', 'like', "%{$keywords}%")
                    ->orWhere('special_id', 'like', "%{$keywords}%");
            })
            ->with(['packs' => function ($q) {
                $q->where('type', 16)
                    ->where('is_used', 1)
                    ->where(function ($q) {
                        $q->where('expire', 0)
                            ->orWhere('expire', '>=', now()->timestamp);
                    });
            }])
            ->addSelect([
                '*',
                DB::raw("((LENGTH(uuid) - LENGTH(REPLACE(uuid, '{$keywords}', ''))) / CHAR_LENGTH(uuid)) * 100 AS matching_percentage")
            ])
            ->orderByDesc('matching_percentage')
            ->first();

        if (!$user || $user?->packs->isNotEmpty()) {
            return [];
        }


        $keywords = $user->id;

        return Room::with('owner')
            ->whereHas('owner', function ($query) {
                $query->where('status', 1);
            })
            ->where('uid', 'like',  $keywords . '%')
            ->whereNotIn('uid', $blockedUserIds)
            ->orderBy('hot', 'desc')
            // ->take(2)
            ->get();
    }

    public function userSearchHand(int $userId, string $keywords, int $page = 1)
    {
        if (!$userId || !$keywords) {
            return [];
        }

        $whereOr = ['uuid' => $keywords];

        $user = Auth::user();
        $blockedByMe = $user->blockedUsers()->pluck('from_uid')->toArray();
        $blockedMe = $user->blockedMe()->pluck('user_id')->toArray();

        $blockedUserIds = array_unique(array_merge($blockedByMe, $blockedMe));


        // $users = User::query()
        // ->select([
        //     '*',
        //     DB::raw("((LENGTH(users.uuid) - LENGTH(REPLACE(users.uuid, '{$keywords}', ''))) / CHAR_LENGTH(users.uuid)) * 100 AS matching_percentage"),
        //      DB::raw("((LENGTH(users.special_id) - LENGTH(REPLACE(users.special_id, '{$keywords}', ''))) / CHAR_LENGTH(users.special_id)) * 100 AS matching_percentage_special_id")
        // ])
        // ->where(function ($query) use ($keywords, $whereOr) {
        //     $query->where(function ($subQuery) use ($keywords) {
        //         $subQuery->where('uuid', 'like', $keywords . '%')
        //                  ->orWhere('special_id', 'like',  $keywords . '%');
        //     })
        //     ->orWhere($whereOr);
        // })
        // ->whereNotIn('id', $blockedUserIds)
        // ->where('status', 1)
        // ->with(['followedByAuthUser', 'country'])
        // ->orderBy('matching_percentage_special_id', 'desc')
        // ->paginate(10, ['*'], 'page', $page);


        // $users = User::query()
        //     ->select([
        //         '*',
        //         DB::raw("
        //     CASE
        //         WHEN uuid = '{$keywords}' THEN 100
        //         WHEN special_id = '{$keywords}' THEN 100
        //         WHEN uuid LIKE '{$keywords}%' THEN 90
        //         WHEN special_id LIKE '{$keywords}%' THEN 90
        //         WHEN uuid LIKE '%{$keywords}%' THEN 80
        //         WHEN special_id LIKE '%{$keywords}%' THEN 80
        //         ELSE 0
        //     END AS relevance_score
        // ")
        //     ])
        //     ->where(function ($query) use ($keywords, $whereOr) {
        //         $query->where(function ($subQuery) use ($keywords) {
        //             $subQuery->where('uuid', 'like', "%{$keywords}%")
        //                 ->orWhere('special_id', 'like', "%{$keywords}%");
        //         })
        //             ->orWhere($whereOr);
        //     })
        //     ->whereNotIn('id', $blockedUserIds)
        //     ->where('status', 1)
        //     ->with(['followedByAuthUser', 'country'])
        //     ->orderByDesc('relevance_score')
        //     ->paginate(10, ['*'], 'page', $page);

        $users = User::query()
            ->select([
                '*',
                DB::raw("
            CASE
                WHEN special_id = '{$keywords}' THEN 100
                WHEN uuid = '{$keywords}' THEN 95
                WHEN special_id LIKE '{$keywords}%' THEN 90
                WHEN uuid LIKE '{$keywords}%' THEN 85
                WHEN special_id LIKE '%{$keywords}%' THEN 80
                WHEN uuid LIKE '%{$keywords}%' THEN 75
                ELSE 0
            END AS match_score
        "),
                DB::raw("
            CASE
                WHEN special_id LIKE '{$keywords}%' THEN LENGTH(special_id)
                ELSE 999
            END AS special_id_length_score
        "),
                DB::raw("
            CASE
                WHEN uuid LIKE '{$keywords}%' THEN LENGTH(uuid)
                ELSE 999
            END AS uuid_length_score
        ")
            ])
            ->where(function ($query) use ($keywords, $whereOr) {
                $query->where(function ($subQuery) use ($keywords) {
                    $subQuery->where('uuid', 'like', "%{$keywords}%")
                        ->orWhere('special_id', 'like', "%{$keywords}%");
                })
                    ->orWhere($whereOr);
            })
            ->whereNotIn('id', $blockedUserIds)
            ->where('status', 1)
            ->with(['followedByAuthUser', 'country'])
            ->orderByDesc('match_score')                 // strong scoring control
            ->orderBy('special_id_length_score')         // shorter = more specific
            ->orderBy('uuid_length_score')               // same here
            ->paginate(10, ['*'], 'page', $page);


        return $users;
    }

    public function getUserFriends(int $userId, string $keywords = null, int $perPage = 10, int $currentPage = 1): \Illuminate\Pagination\LengthAwarePaginator
    {

        $usersQuery = User::query()
            ->whereHas('followers', fn($q) => $q->where('user_id', $userId))
            ->whereHas('followeds', fn($q) => $q->where('followed_user_id', $userId));

        if ($keywords) {
            $usersQuery->where(function ($q) use ($keywords) {
                $q->where('name', 'like', '%' . $keywords . '%')
                    ->orWhere('uuid', 'like', '%' . $keywords . '%')
                    ->orWhere('id', 'like', '%' . $keywords . '%');
            });
        }

        return $usersQuery->with([
            'packs' => function ($query) {
                $query->whereIn('type', [4, 18]);
            },
            'profile',
            'UserVip'
        ])
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function getSearchList(int $userId): array
    {
        $hot = DB::table('search_histories')
            ->select(['id', 'search'])
            ->where('type', 1)
            ->orderBy('sort', 'desc')
            ->get();

        $history = DB::table('search_histories')
            ->select(['id', 'search'])
            ->where('type', 2)
            ->where('user_id', $userId)
            ->get();

        return [
            'hot' => $hot,
            'history' => $history,
        ];
    }

    public function clearUserSearchHistory(int $userId): bool
    {
        return DB::table('search_histories')
            ->where('type', 2)
            ->where('user_id', $userId)
            ->delete();
    }

    public function getOfficialMessages(int $userId, int $page = 1): array
    {
        $sys = OfficialMessage::query()
            ->whereIn('user_id', [0, $userId])
            ->where('type', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $official = OfficialMessage::query()
            ->whereIn('user_id', [0, $userId])
            ->where('type', 2)
            ->orderBy('created_at', 'desc')
            ->get();

        $agency = OfficialMessage::query()
            ->whereIn('user_id', [0, $userId])
            ->where('type', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'sys' => CommunityResource::collection($sys),
            'official' => CommunityResource::collection($official),
            'agency' => CommunityResource::collection($agency),
        ];
    }
}
