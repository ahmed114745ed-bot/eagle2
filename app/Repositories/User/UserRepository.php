<?php
namespace App\Repositories\User;

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

    public function updateDeviceToken($user, $deviceToken = null)
    {
        if ($user->device_token != $deviceToken) {
            $user->device_token = $deviceToken;
            $user->save();
        }
    }

    public function updateOnlineTime(User $user, $currentTime)
    {
        $user->online_time = $currentTime;
        $user->save();
    }

    public function getUserWithMedals($userId)
    {
        return User::with(['medals'=> fn($q) => $q->userPickProfile(),'userSetting','ownAgency'
                            ,'agencyUserJob'=> fn($q)=>$q->where('type', 'requestManger')
                            ,'agencyJoinRequest'=> fn($q)=>$q->where('status', '!=', 2)
                            ,'packs'
                            ])
                    ->find($userId);
    }

    public function updateLocation($userId,$lat,$long)
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

}
