<?php

namespace App\Tik\Repositories;

use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;


class UserRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new User());
    }

    public function create(array $data): mixed
    {
        return  '';
    }


    public function searchUser($userUuId)
    {
        return $this->model->searchByUuid($userUuId)->first();
    }


    public function incrementCoins($userUuIdOrId, $coins)
    {
        $user = $this->searchUser($userUuIdOrId) ?? $this->findById($userUuIdOrId);
        $this->incrementUserCoins($user, $coins);
        return true;
    }

    public function decrementCoins($userUuIdOrId, $coins)
    { 
        $user =$this->searchUser($userUuIdOrId) ?? $this->findById($userUuIdOrId);
        $this->decrementUserCoins($user, $coins);
        return true;
    }

    public function incrementUserCoins($user, $coins)
    {
        $user->increment('di', $coins);
        return true;
    }

    public function decrementUserCoins($user, $coins)
    {
        $user->decrement('di', $coins);
        return true;
    }

    public function incrementUnreadMessage($user)
    {
        $user->increment('unread_count_message');
        return true;
    }

    public function findById($id)
    {
        return $this->model->with('profile')->find($id);
    }

    public function updateUnreadCountMessage($user)
    {
        $user->unread_count_message = 0;
        $this->updateUser($user);
    }

    public function updateUser($user)
    {
        $user->save();
    }

    public function getUsers($ids)
    {
        return $this->model->query()->whereIn('id', $ids)->get();
    }

    public function getUsersWithPaginate($ids, $paginate)
    {
        return $this->model->query()->whereIn('id', $ids)->paginate($paginate);
    }


    public function usersRoom($roomAdminActive)
    {
        return $this->model->withoutAppends()->select([
            '*', DB::raw('(SELECT  level from users_vips
                     where (users_vips.expire >= UNIX_TIMESTAMP()) and users_vips.user_id = users.id order by level  desc limit 1) as max_level
                     ')
        ])->with([
            'packs' => function ($query) {
                return $query->whereIn('type', [4, 18, 5, 17]);
            }, 'profile', 'UserVip', 'dress1',
        ])->where(function ($query) {
            $query->whereDoesntHave('packs')->orWhereHas('packs', function ($q) {
                $q->where("type", '!=', 17)->orWhere(fn ($q) => $q->where('type', 17)->where("is_used", 0));
            });
        })->whereIn('users.id', $roomAdminActive)->orderByDesc(DB::raw('max_level'));
    }

    public function anotherUserRoom($roomVisitorArray, $limit, $offset)
    {
        return  $this->usersRoom($roomVisitorArray)->limit($limit)->offset($offset)->get();
    }

    public function updateFamilyId($user, $familyId)
    {
        $user->update(['family_id', $familyId]);
        return true;
    }

    public function findByPhoneUser($phone)
    {
        return $this->model->query()->where('phone', $phone)->first();
    }

    public function updateDeviceToken($user, $deviceToken)
    {
        $user->device_token = $deviceToken;
        $this->updateUser($user);
        return true;
    }

    public function updateIsLogout($user, $isLogout)
    {
        $user->lan = app()->getLocale() ?? 'en';
        $user->is_logout = $isLogout;
        $user->is_points_first = 1;
        $this->updateUser($user);
    }

    public function findByGoogleId($googleId)
    {
        return $this->model->query()->whereNotNull('google_id')->where('google_id', $googleId)->first();
    }
    public function checkTrashedEmail($email, $googleId)
    {
        return $this->model->withTrashed()->where(fn ($q) => $q->whereNotNull('email')->where('email', $email))->orWhere('google_id', $googleId)->exists();
    }

    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByHuawei($huaweiId)
    {
        return $this->model->query()->whereNotNull('huawei_id')->where('huawei_id', $huaweiId)->first();
    }

    public function checkEmail($email)
    {
        return $this->model->query()->whereNotNull('email')->where('email', $email)->exists();
    }

    public function findByTrashedEmail($email, $googleId)
    {
        return $this->model->onlyTrashed()->where(fn ($q) => $q->whereNotNull('email')->where('email', $email))->orWhere('google_id', $googleId)->first();
    }

    public function checkByGoogleId($userId, $googleId)
    {
        return $this->model->query()->where('google_id', $googleId)->where('id', '!=', $userId)->exists();
    }
    public function checkByFaceBookId($userId, $facebookId)
    {
        return $this->model->query()->where('facebook_id', $facebookId)->where('id', '!=', $userId)->exists();
    }

    public function checkByPhone($userId,$phone)
    {
        return $this->model->query()->where('phone', $phone)->where('id', '!=', $userId)->exists();
    }
}
