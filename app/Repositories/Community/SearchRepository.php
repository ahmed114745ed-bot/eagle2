<?php
namespace App\Repositories\Community;

use App\Helpers\Common;
use App\Http\Resources\Api\V1\CommunityResource;
use App\Models\OfficialMessage;
use App\Models\User;
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

    public function searchRooms(int $userId, string $keywords, int $page = 1): array
    {
        $user = User::where('uuid', $keywords)->first();

        if (!$user) {
            return [];
        }

        $keywords = $user->id;

        $rooms = DB::table('rooms')
            ->join('users', 'rooms.uid', '=', 'users.id')
            ->where('rooms.uid', 'like', '%' . $keywords . '%')
            ->where('users.status', 1)
            ->select([
                'rooms.room_name',
                'rooms.uid',
                'rooms.numid',
                'rooms.hot',
                'rooms.room_cover',
                'rooms.room_intro',
                'rooms.room_welcome',
                'rooms.room_pass',
                'users.nickname',
                'users.name'
            ])
            ->orderBy('rooms.hot', 'desc')
            ->forPage($page, 10)
            ->get();

        return $rooms->map(function ($room) {
            $room->hot = Common::room_hot($room->hot);
            $room->nickname = $room->nickname ?: '';
            $room->room_cover = $room->room_cover ?: '';
            $room->room_pass = !empty($room->room_pass);

            return $room;
        })->toArray();
    }

    public function userSearchHand(int $userId, string $keywords, int $page = 1)
    {
        if (!$userId || !$keywords) {
            return [];
        }

        $whereOr = ['uuid' => $keywords];

        $users = User::query()
            ->select(['*', DB::raw("((LENGTH(users.uuid) - LENGTH(REPLACE(users.uuid, '{$keywords}', ''))) / CHAR_LENGTH(users.uuid)) * 100 AS matching_percentage")])
            ->where(function ($query) use ($keywords) {
                $query->where('uuid', 'like', '%' . $keywords . '%')
                      ->orWhere('special_id', 'like', '%' . $keywords . '%');
            })
            ->where('status', 1)
            ->orWhere(function ($query) use ($whereOr) {
                $query->where($whereOr);
            })
            ->orderBy('matching_percentage', 'desc')
            ->forPage($page, 10)
            ->get();

        foreach ($users as $user) {
            $user->is_follow = DB::table('follows')
                ->where('user_id', $userId)
                ->where('followed_user_id', $user->id)
                ->where('status', 1)
                ->exists() ? 1 : 0;
        }

        return $users;
    }

    public function getUserFriends(int $userId, string $keywords = null, int $perPage = 10, int $currentPage = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        $usersQuery = User::query()
            ->whereHas('followers', fn($q) => $q->where('user_id', $userId))
            ->whereHas('followeds', fn($q) => $q->where('followed_user_id', $userId));

        if ($keywords) {
            $usersQuery->where(function($q) use ($keywords) {
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
