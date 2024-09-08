<?php

namespace App\Tik\Repositories;

use App\Models\Room;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use Modules\Chat\Jobs\SendMessageToAllUsers;


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
        return $this->model->query()->with(['agency', 'profile', 'family'])->whereIn('id', $ids)->get();
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
    public function updateTypeUser($user)
    {
        $user->type_user = 1;
        $this->updateUser($user);
    }

    public function findUsersByAgencyId($agencyId, $perPage, $page)
    {
        return $this->model->where('agency_id', $agencyId)->orderBy('monthly_diamond_received', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getIdsByAgencyId($agencyId)
    {
        return $this->model->query()->where('agency_id', $agencyId)->pluck("id")->toArray();
    }

    public function getAgencyMangerByFilter($keyword)
    {
        return $this->model->query()->select(['*', DB::raw("((LENGTH(users.uuid) - LENGTH(REPLACE(users.uuid, '{$keyword}', ''))) / CHAR_LENGTH(users.uuid)) * 100 AS matching_percentage")])->has('ownAgency')->where(function ($q) use ($keyword) {
            $q->where('uuid', 'like', '%' . $keyword . '%')
                ->orWhereHas('ownAgency', function ($query) use ($keyword) {
                    $query->where('id', 'like', '%' . $keyword . '%');
                });
        })->orderBy('matching_percentage', 'desc')->take(10)->get();
    }

    public function getUsersByJoinAgency($agencyId)
    {
        return $this->model->where("agency_id", $agencyId)->where("type_user", '!=', 0)->whereMonth("join_agency_date", date("m"))->get();
    }

    public function getByHost($agencyId, $hostId = null)
    {
        $hosts = $this->model->where("agency_id", $agencyId)->where("type_user", '!=', 0);
        if ($hostId != null) {
            $hosts = $hosts->where("id", $hostId);
        }
        $hosts = $hosts->get();
    }

    public function updateShowGift($user)
    {
        $user->stopshow_gift = !$user->stopshow_gift;
        $this->updateUser($user);
    }

    public function logout($user)
    {
        $user->is_logout = 1;
        $this->updateUser($user);
        $user->currentAccessToken()->delete();
        return true;
    }

    public function updateDress($user, $dressType, $packType, $wareId)
    {
        $user->update(['dress_' . $dressType[$packType] => $wareId]);
        return true;
    }

    public function nullDress($user, $type)
    {
        $user->update(['dress_' . $type => null]);
        return true;
    }

    public function getUsersById($ids, $coins)
    {
        return $this->model->whereIn("id", $ids)->where("di", "<", $coins)->pluck("name")->toArray();
    }

    public function findUser($id)
    {
        return $this->model->whereId($id)->first();
    }

    public function pluckUsersByIds($ids, $type)
    {
        return $this->model->whereIn("id", $ids)->pluck($type)->toArray();
    }

    public function getChatIds($user)
    {
        return $user->chats->pluck('id')->toArray();
    }

    public function updateCurrentChat($user, $chatRoomId)
    {
        $user->current_room_chat = $chatRoomId;
        $this->updateUser($user);
    }

    public function getByName($name)
    {
        return $this->model->where('name', 'LIKE', '%' . $name . '%')->select('id', 'name', 'img')->get();
    }

    public function userChatRoom($userId,$data,$exceptId)
    {
        $this->model->query()
        ->select('id')
        ->whereHas('followers', fn($q) => $q->where('user_id', $userId))
        ->whereHas('followeds', fn($q) => $q->where('followed_user_id', $userId))
        ->whereNotIn('id', $exceptId)
        ->chunk(400, function($userIds) use($userId, $data){
            $userIds = $userIds->pluck('id')->toArray();
            $this->sendMessageToUsers($userId, $userIds, $data);
        });
    }

    public function sendMessageToUsers(int|string|null $userId, mixed $userIds, array $message): void
    {
        $timeZone = request()->hasHeader('tz') ? request()->header()['tz'][0] : 'UTC';
        dispatchJobToQueue(new SendMessageToAllUsers($userId, $userIds, $message, timezone: $timeZone), 'heavyProcessing');

    }
}
