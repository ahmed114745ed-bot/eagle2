<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Vip;
use App\Models\User;
use App\Models\Follow;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Facades\UserHandling;
use App\Http\Services\WhatsappOtp;
use App\Facades\CustomNotification;
use App\Repositories\PackRepository;
use App\Http\Services\WhatsappWebhook;
use App\Repositories\FollowRepository;
use App\Http\Services\RoomGameServices;
use App\Tik\Repositories\VipRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Database\Query\JoinClause;
use App\Http\Resources\Api\V1\RoomResource;
use App\Tik\Repositories\GiftLogRepository;
use App\Tik\Repositories\UserSettingRepository;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Tik\Repositories\ProfileVisitorRepository;
use App\Http\Resources\Api\V1\UserRelationsResource;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;

class UserService
{
    protected $userRepository;
    protected $packRepository;
    protected $followRepository;

    public function __construct(
        private readonly VipRepository $vipRepository,
        private readonly ProfileVisitorRepository $ProfileVisitorRepository,
        private readonly UserSettingRepository $userSettingRepository,
      private readonly  GiftLogRepository $giftLogRepository,
        UserRepository $userRepository,
        PackRepository $packRepository,
        FollowRepository $followRepository,

    ) {
        $this->userRepository = $userRepository;
        $this->packRepository = $packRepository;
        $this->followRepository = $followRepository;
    }

    public function searchUsers($key)
    {
        $perPage = 10;
        $currentPage = request()->has('page') ? request()->page : 1;

        return $this->userRepository->search($key, $perPage, $currentPage);
    }

    public function searchUsersWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchWithPage($key, $page, $perPage);
    }

    public function searchUsersInAgency($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchUserAgency($key, $page, $perPage);
    }

    public function bind($user, $request)
    {
        if ($request->google_id && !$user->google_id) {
            $ex = $this->userRepository->checkByGoogleId($user->id, $request->google_id);
            if ($ex) throw new \Exception('this google_account is restricted with another account');
            $user->google_id = $request->google_id;
        }

        if ($request->facebook_id && !$user->facebook_id) {
            $ex = $this->userRepository->checkByFaceBookId($user->id, $request->facebook_id);
            if ($ex) throw new \Exception('this facebook_account is restricted with another account');
            $user->facebook_id = $request->facebook_id;
        }

        if ($request->phone && !$user->phone) {
            $ex = $this->userRepository->checkByPhone($user->id, $request->phone);
            if ($ex) return throw new \Exception('this phone is restricted with another account');
            if (!$request->has('is_whatsapp')) {
                if (!$request->code || !$request->password) throw new \Exception('missing params');

                $whatsappOtpService = new WhatsappOtp();
                $phone              = $request->phone;
                $isValid            = $whatsappOtpService->isValidate($phone, $request->code);
                if (!$isValid)  throw new \Exception(__('api_responses.invalid_code'));
                $whatsappOtpService->resetCodes($phone); // $code = Code::query ()->where ('phone',$request->phone)->where('code',$request->vr_code)->first ();
                // if (!$code) return Common::apiResponse (0,'this phone not verified',null,310);

            } else {
                if (!$request->password) throw new \Exception('missing params');
                $whatsappWebhookValidate = (new WhatsappWebhook())->getLastValidatedPhone($request->phone);
                if (!$whatsappWebhookValidate) throw new \Exception(__('current phone not verified'));
            }
            $user->phone    = $request->phone;
            $user->password = $request->password;   // $code->delete ();
            UserHandling::AddUserVip($user, 'join_account');
        }
        $this->userRepository->updateUser($user);
        return true;
    }

    public function processUserData($user, $deviceToken, $lat, $long)
    {
        $this->userRepository->updateDeviceToken($user, $deviceToken);

        $currentTime = time();
        // $this->dailyPrizeService->reset(Carbon::createFromTimestamp($user->real_online_time), $user->id);

        $this->userRepository->updateOnlineTime($user, $currentTime);
        // update location
        if (is_numeric($lat) && $lat >= -90 && $lat <= 90 && is_numeric($long) && $long >= -180 && $long <= 180) {
            $this->userRepository->updateLocation($user->id, $lat, $long);
        }
        // end update location

        $userWithMedals = $this->userRepository->getUserWithMedals($user->id);

        return $userWithMedals;
    }

    public function unlockDressHand($userId)
    {
        $vip = Common::getLevel($userId, 3);
        $types = [4, 5, 6, 7, 8];
        $ids = $this->packRepository->getTargetIdsByUserAndType($userId, $types);

        $wares = $this->packRepository->getWaresByConditions($vip, $types, $ids);

        if ($wares->isEmpty()) return 0;

        foreach ($wares as $ware) {
            $pack = $this->packRepository->getExistingPack($userId, $ware->type, $ware->id);
            if ($pack) continue;

            $data = [
                'user_id'   => $userId,
                'type'      => $ware->type,
                'target_id' => $ware->id,
                'expire'    => $ware->expire ? time() + ($ware->expire * 86400) : 0,
                'is_read'   => 1,
            ];

            $this->packRepository->createPack($data);
        }
        return count($wares);
    }

    public function updateLocation($userId, $lat, $log)
    {
        $this->userRepository->updateLocation($userId, $lat, $log);
    }

    public function toggleLike($userId, $likedUserId)
    {
        $hasLiked = $this->userRepository->hasLiked($userId, $likedUserId);

        if ($hasLiked) {
            $this->userRepository->detachLike($userId, $likedUserId);
            return __("liked deleted successfully");
        } else {
            $this->userRepository->attachLike($userId, $likedUserId);
            return __("liked added successfully");
        }
    }

    public function toggleIgnored($userId, $likedUserId)
    {
        $hasLiked = $this->userRepository->hasIgnored($userId, $likedUserId);

        if ($hasLiked) {
            $this->userRepository->detachIgnored($userId, $likedUserId);
            return __("ignored deleted successfully");
        } else {
            $this->userRepository->attachIgnored($userId, $likedUserId);
            return __("ignored added successfully");
        }
    }

    public function handleUserRelations($user, $type)
    {
        switch ($type) {
            case '1':
            case '2':
            case '3':
            case '6':
                (new UserCounterServices)->UpgradeDateForType($user, 'friend');
                return Common::apiResponse(true, '', $this->getData2($user, $type), 200);

            case '4':
                (new UserCounterServices)->UpgradeDateForType($user, 'followeds');
                return Common::apiResponse(true, '', UserRelationsResource::collection($this->userRepository->getFolloweds($user)), 200);

            case '5':
                $followRooms = $this->userRepository->getFollowRooms($user->id);
                return Common::apiResponse(true, '', RoomResource::collection($followRooms), 200);

            default:
                return Common::apiResponse(false, 'please select type', null, 422);
        }
    }

    public function followUser($request)
    {
        $userId = $request->user()->id;
        $followedUserId = $request->user_id;

        if ($userId == $followedUserId) {
            return Common::apiResponse(false, 'cant follow your self', null, 403);
        }

        $receiver = $this->followRepository->findUserById($followedUserId);
        if (!$receiver) {
            return Common::apiResponse(false, 'this user not found', null, 404);
        }

        $follow = $this->followRepository->findFollow($userId, $followedUserId);

        if (!$follow) {
            $this->followRepository->createFollow([
                'user_id' => $userId,
                'followed_user_id' => $followedUserId,
                'status' => 1
            ]);

            $this->handleFollowBack($request->user(), $receiver);
        } else {
            $this->followRepository->updateFollowStatus($follow, 1);
        }

        return Common::apiResponse(true, 'follow done', null, 201);
    }

    public function unFollowUser($request)
    {
        $this->followRepository->deleteFollow($request->user()->id, $request->user_id);
        return Common::apiResponse(true, 'unFollow done', null, 201);
    }

    protected function handleFollowBack($user, $receiver)
    {
        if ($user->followBack($receiver)) {
            CustomNotification::followBack($receiver, $user);
            (new UserCounterServices)->eventUser($receiver, 'friend', 1);
        } else {
            CustomNotification::follow($receiver, $user);
            (new UserCounterServices)->eventUser($receiver, 'follow', 1);
        }
        (new UserCounterServices)->eventUser($receiver, 'follower', 1);
    }


    public function getData(User $user, $type = 1)
    {
        $userId = $user->id;

        if ($type == 1) {
            // following in app
            $data = $this->followRepository->getByFollowed($userId);
            $collect = collect($data->items());
            $users    = $collect->pluck('followed');
        } elseif ($type == 2) {
            $data = $this->followRepository->getByFollower($userId);
            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        } elseif ($type == 3) {
            $data = $this->followRepository->getByFriends($userId);
            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        }
        elseif ($type == 6) {
            // uses that follow you not friend with you
            $users = $this->followRepository->getFollow($userId);

        } else {
            $users = collect([]);
        }


        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $users);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($users);
    }


    public function getData2(User $user, $type = 1)
    {
        $userId = $user->id;

        $with = [
            'room' => function ($query) {
                return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
            },
            'followPacks',
            'profile',
            'ware',
            'UserVip'
        ];

        if ($type == 1) {
            // following in app
            $users = $this->followRepository->getFollowing($user, $with);
        } elseif ($type == 2) {
            $users = $this->followRepository->getFollowers($user, $with);
        } elseif ($type == 3) {
            $users = $this->followRepository->getFriends($user, $with);
        }
        elseif ($type == 6) {
            // uses that follow you not friend with you
            $users = $this->followRepository->getFollow($userId);

        } else {
            $users = collect([]);
        }


        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $users);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($users);
    }

    public function getHelperArrays(User $user, $data): array
    {
        $userFollowers = $this->followRepository->getFollowedIds($user->id);
        [$vipsSenderImages, $vipsReceivedImages] =
            $this->getLevelsSenderAndReceiver($data);

        return [$userFollowers, $vipsSenderImages, $vipsReceivedImages];
    }

    public function getLevelsSenderAndReceiver($data): array
    {
        $vipsSenderImages   = $data->pluck('total_sender_level');
        $vipsReceivedImages = $data->pluck('total_received_level');

        $vipsSenderImages   = $this->getLevel($vipsSenderImages, 2);
        $vipsReceivedImages = $this->getLevel($vipsReceivedImages);
        return [$vipsSenderImages, $vipsReceivedImages];
    }
    public function getLevel($levelsList, $type = 1)
    {
        return $this->vipRepository->getByLevels($levelsList,$type);
    }

    public function myStore($user, $request)
    {
        $cacheKey = 'cache-data-mystore-' . $user->id;
        if (\Cache::add($cacheKey, true, now()->addSeconds(30))) {

            $targetService = new FixedTargetService($user);
            $targetService->calculateTarget();
            if ($user->ownerRoom != null) {
                $roomTarget = new RoomGameServices();
                $roomTarget->CalculateRoomSalaries($user->ownerRoom);
            }
        }

        if ($user->device_token  != $request->header('device')) {
            $user->enableSaving = true;
            $user->device_token = $request->header('device');
            $user->save();
        }

        return $user;
    }

    public function showUser($userId, $auth, $request, $isVisit)
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) throw new \Exception('not found');
        if (in_array($user->id, Common::getUserBlackList($auth->id))) throw new \Exception('in black list');
        if (in_array($auth->id, Common::getUserBlackList($user->id))) throw new \Exception('in black list');
        $request['user_id'] = $userId;

        if ($auth->id != $user->id && $isVisit == true) {
            if (!Common::checkPackPrev($auth->id, 19)) {
                $previousVisit = $this->ProfileVisitorRepository->checkVisit($auth->id, $user->id);
                $user->profileVisits()->syncWithoutDetaching(
                    [
                        $auth->id => [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]
                );

                if ($isVisit == true) {

                    if (!$previousVisit) {
                        CustomNotification::visitProfile($user, $auth);
                        (new UserCounterServices)->eventUser($user, 'visit-profile');
                    }
                }
            }
        }
        return $user;
    }

    public function roomRanking($request)
    {
        $type     = $request->input('type', 2);
        $room_uid = $request->input('room_uid');
        $limit    = $request->input('is_home') ? 3 : 30;
        $user_id  = $request->user()->id;
        $query = GiftLog::query()->where('roomowner_id', $room_uid);

        if ($type == 1) {
            $query = $query->whereBetween('created_at', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        }

        $data = $query->selectRaw("SUM(giftPrice) as exp, sender_id")
            ->groupBy('sender_id')
            ->orderByRaw("exp desc")
            ->limit($limit)
            ->get()
            ->reject(function ($q) {
                return $q->exp == 0;
            });

        $i = $l = 0;
        $achivement      = new UserAchievementService();

        foreach ($data as $k => &$v) {
            $user = User::find($v->sender_id);
            if (!$user) {
                $v->user_id  = 0;
                $v->exp      = ceil($v->exp);
                $v->name     = $user ? $user->name : '';
                $v->avatar   = '';
                $v->frame    = '';
                $v->frame_id = 0;
                $v->type_user = 0;
                $v->manger_type = null;
                $v->vip = null;
                $v->has_color_name = null;
                $v->data_achivement = null;
                continue;
            }
            $i++;
            $v->user_id  = $v->sender_id;
            $v->exp      = ceil($v->exp);
            $v->name     = $user ? $user->name : '';
            $v->avatar   = $user && $user->profile ? $user->profile->avatar : '';
            $v->frame    = $user ? Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true) : '';
            $v->frame_id = $user ? $user->dress_1 : '';
            $v->type_user = intval(@$user->type_user) ?: 0;
            $v->manger_type = !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);
            $v->vip = @Common::ovip_center($user);
            $v->has_color_name = Common::hasInPack($user->id, 18, true);
            $v->data_achivement = UserAchievementLevelsResource::collection($achivement->getUserAchievement($user));

            if ($v->sender_id == $user_id) {
                $l = $i;
            }

            unset($v->sender_id);
        }

        unset($v);

        return  $data->toArray();
    }

    public function resetWhatsapp($request, $whatsappWebhook)
    {
        $phone = $request->phone;
        $whatsappWebhookValidate = $whatsappWebhook->getLastValidatedPhone($phone);
        if (!$whatsappWebhookValidate)throw new \Exception( __('current phone not verified'));

        $user = User::query ()->where ('phone', $phone)->first ();

        $user->password = $request->password;
        $user->save();
        return $user;
    }

    public function allUsers($search)
    {
        return $this->userRepository->UsersWithSearch($search);
    }

    public function userInfoWithRoles($ownerId)
    {
       $user = $this->userRepository->findUserById($ownerId);
    }

    public function setting($userId, $request)
    {
        $setting = $this->userSettingRepository->userSitting($userId);

        if ($setting != null) {
            $key = $request->key;
            $this->userSettingRepository->updateKey($setting, !$setting->$key);
            $setting->$key = !$setting->$key;
            $setting->save();
        } else {

            $data = [
                'user_id'      => $userId,
                'show_git'     => $request->chat_with_friends ?? 1,
                'show_intro'   => $request->chat_with_followers ?? 1,
                'show_banner'  => $request->chat_with_all ?? 1,
            ];
            $this->userSettingRepository->create($data);
        }

        return $setting;
    }

    public function supporter($userId)
    {
        return $this->giftLogRepository->getByUserId($userId);
    }
}
