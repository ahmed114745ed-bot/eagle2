<?php

namespace App\Repositories\User;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\UserRepository as Repository;

class UserRepository extends Repository
{
    public function search($key, $perPage, $currentPage)
    {
        return User::query()
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('uuid', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->select(['id', DB::raw('concat(name , " - ", uuid) as name')])
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function searchWithPage($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('uuid', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchWithPageNew($key, $page, $perPage)
    {

        return User::select('id', 'uuid', 'name')
            ->where('name', 'like', '%' . $key . '%')
            ->orWhere('uuid', 'like', '%' . $key . '%')
            ->orWhere('id', 'like', '%' . $key . '%')
            ->paginate($perPage, ['*'], 'page', $page);
    }


    public function searchUserAgency($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('agency_id', 0)
                    ->orWhereNull('agency_id');
            })
            ->where('type_user', 0)
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function searchUserFamily($key, $page, $perPage)
    {
        return User::selectRaw('concat(name, " - ", uuid) as name, id')
            ->where(function ($query) {
                $query->where('family_id', 0)
                    ->orWhereNull('family_id');
            })
            ->where(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->orWhere('uuid', 'like', '%' . $key . '%')
                    ->orWhere('id', 'like', '%' . $key . '%');
            })
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function updateDeviceToken($user, $deviceToken = null)
    {
        if (is_null($user->device_token) || $user->device_token != $deviceToken) {
            $user->device_token = $deviceToken;
            $user->save();
        }
    }

    public function updateOnlineTime(User $user, $currentTime)
    {
        $user->online_time = $currentTime;
        $user->lan = app()->getLocale();
        $user->save();
    }

    public function getUserWithMedals($userId)
    {
        return User::with([
            'medals' => fn($q) => $q->userPickProfile(),
            'userSetting',
            'ownAgency',
            'agencyUserJob' => fn($q) => $q->where('type', 'requestManger'),
            'agencyJoinRequest' => fn($q) => $q->where('status', '!=', 2),
            'packs',
            'country'
        ])
            ->find($userId);
    }

    public function userCharge()
    {
        return User::where('type_user', 3)->orWhere('type_user', 4)->orderByDesc('id')->paginate(10);
    }

    public function updateLocation($userId, $lat, $long)
    {
        User::whereId($userId)->update([
            "lat"   => $lat,
            "long"  => $long,
        ]);
    }

    public function findUserById($id)
    {
        return User::find($id);
    }

    public function findUserByUuid($uuid)
    {
        return User::where('uuid', $uuid)->first();
    }

    public function logProfileVisit($user, $visitorId)
    {
        $user->profileVisits()->syncWithoutDetaching([
            $visitorId => [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function hasLiked($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        return $user->likes()->where('liked_user_id', $likedUserId)->exists();
    }


    public function attachLike($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->likes()->attach($likedUserId);
    }

    public function detachLike($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->likes()->detach($likedUserId);
    }

    public function hasIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        return $user->ignores()->where('ignore_user_id', $likedUserId)->exists();
    }


    public function attachIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->ignores()->attach($likedUserId);
    }

    public function detachIgnored($userId, $likedUserId)
    {
        $user = $this->findUserById($userId);
        $user->ignores()->detach($likedUserId);
    }

    public function decrementBalance(User $user, $amount)
    {
        $user->decrement('di', $amount);
    }

    public function getFollowers($user, $type)
    {
        // Query to get followers based on the type
        return User::whereHas('followers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->paginate(15);
    }

    public function getFolloweds($user)
    {
        return $user->onRoomFolloweds();
    }

    public function getFollowRooms($userId)
    {
        return Follow::query()->whereHas('room', function ($query) {
            $query->withoutAppends()->where('count_room_socket', '!=', 0);
        })->with([
            'room' => function ($query) use ($userId) {
                $query->withoutAppends()->with([
                    'owner' => function ($query) {
                        $query->withoutAppends();
                    }
                ]);
            }
        ])->where('user_id', $userId)->orderByDesc('id')->paginate(10)->pluck('room');
    }

    public function updateUserGame($user, $gameId)
    {
        $user->game_id = $gameId;
        return $user->save();
    }

    public function UsersWithSearch($search)
    {
        return $this->model->query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('id', $search)
                        ->orWhere('uuid', $search);
                });
            })->select('id', 'name', 'uuid')->get();
    }

    public function nearUsers($userId, $latitude, $longitude)
    {
        return  User::query()
            ->with('profile')
            ->select(
                'users.*',
                DB::raw("(6371 * acos(cos(radians($latitude))
                * cos(radians(users.lat))
                * cos(radians(users.long) - radians($longitude))
                + sin(radians($latitude))
                * sin(radians(users.lat)))) AS distance")
            )->whereNotNull('lat')->whereNotNull('long')
            ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $userId))
            ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $userId)])
            ->where('id', '!=', $userId)->orderBy('distance', 'asc')->paginate(10);
    }

    public function users($userId, $latitude = null, $longitude = null)
    {
        $builder = User::query()
            ->with('profile')
            ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $userId))
            ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $userId)])
            ->where('id', '!=', $userId);
        if (($latitude != null) && ($longitude != null)) {
            $builder = $builder->select(
                'users.*',
                DB::raw("(6371 * acos(cos(radians($latitude))
                    * cos(radians(users.lat))
                    * cos(radians(users.long) - radians($longitude))
                    + sin(radians($latitude))
                    * sin(radians(users.lat)))) AS distance")
            )->whereNotNull('lat')->whereNotNull('long');
        }
        return     $builder->paginate(10);
    }


    public function trashedUserAccountList($perPage, $Page, $search, $id)
    {
        return User::onlyTrashed()->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('phone', 'LIKE', "%$search%")
                    ->orWhere('uuid', 'LIKE', "%$search%");
            });
        })->when($id, function ($query) use ($id) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id);
            });
        })->orderByDesc('deleted_at')->paginate($perPage, ['*'], 'page', $Page);
    }

    public function restoreAccount($id)
    {
        $user = User::query()->onlyTrashed()->find($id);
        $user->restore();
        return true;
    }

    public function softDelete($id)
    {
        $user = User::query()->onlyTrashed()->find($id);
        DB::table('reports')->where('Reporter_id', $user->id)->delete();
        $user->forceDelete();
        return true;
    }

    public function userLevel($perPage, $Page, $search)
    {
        return User::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uuid', $search)
                    ->orWhere('phone', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        })->paginate($perPage, ['*'], 'page', $Page);
    }

    public function all($perPage, $Page, $familyId, $agencyId, $search, $host)
    {
        return User::when(isset($familyId), function ($query) use ($familyId) {
            $query->where('family_id', $familyId);
        })->when(isset($agencyId), function ($query) use ($agencyId) {
            $query->where('agency_id', $agencyId);
        })->when(isset($host), function ($query) use ($host) {
            $query->where('is_host', $host);
        })->when(isset($search), function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('uuid', 'like', "%$search%")->orWhere('special_id', 'like', "%$search%")->orWhere('phone', 'like', "%$search%")->orWhere('nickname', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
        })->orderByDesc('id')->with('agency', 'targets')->paginate($perPage, ['*'], 'page', $Page);
    }
}
