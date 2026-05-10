<?php

namespace App\Services;

use DB;
use Cache;
use Exception;
use Carbon\Carbon;
use App\Models\Code;
//use Utd\Agency\Repositories\ShippingAgencyRepository;
use Utd\Chat\Entities\ChatRoom;
use Utd\Room\Entities\Room;
use App\Models\User;
use App\Helpers\Common;
use App\Models\Country;
use Utd\Gifts\Entities\GiftLog;
use App\Jobs\UserVisitJob;
use App\Facades\UserHandling;
use App\Enums\UserCoinLogType;
use App\helper\UserFollowHelper;
use App\Helpers\UserCoinLogHelper;
use App\Http\Services\WhatsappOtp;
use App\Models\ChangeLevelHistory;
use App\Facades\CustomNotification;
use App\Support\PackageHelper;
use App\Repositories\PackRepository;
use App\Http\Services\WhatsappWebhook;
use App\Repositories\FollowRepository;
use Utd\Bd\Repositories\BdRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Config;
use App\Contracts\RoomGameContract;
use App\Tik\Repositories\WareRepository;
use App\Repositories\BlackListRepository;
use App\Repositories\User\UserRepository;
use App\Contracts\CpRepositoryContract;
use Utd\Agency\Repositories\AgencyRepository;
use App\Tik\Repositories\TargetRepository;
use App\Http\Resources\Api\V1\RoomResource;
use App\Contracts\FamilyContract;
use App\Contracts\VipRepositoryContract;
use App\Tik\Repositories\UserSalaryRepository;
use App\Tik\Repositories\UserTargetRepository;
use App\Contracts\RoomVisitorRepositoryContract;
use App\Tik\Repositories\UserSettingRepository;
use Utd\Agency\Repositories\AgencySalaryRepository;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Http\Resources\InvitationEarningResource;
use App\Tik\Repositories\ProfileVisitorRepository;
use App\Contracts\ShippingAgencyRepositoryInterface;
use App\Http\Resources\Api\V1\UserRelationsResource;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\Public\Http\Services\UserCounterServices;
use App\Contracts\UserDevicesHistoryRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Contracts\UserAchievementContract;
use Utd\Achievements\Transformers\UserAchievementLevelsResource;
use App\Contracts\GiftLogRepositoryContract;

class UserService
{
    public function __construct(
        private readonly VipRepositoryContract $vipRepository,
        private readonly ProfileVisitorRepository $ProfileVisitorRepository,
        private readonly UserSettingRepository $userSettingRepository,
        private readonly ?GiftLogRepositoryContract $giftLogRepository,
        private readonly UserSalaryRepository $userSalaryRepository,
        private readonly TargetRepository $targetRepository,
        private readonly UserDevicesHistoryRepositoryContract $userDevicesHistoryRepository,
        private readonly UserTargetRepository $userTargetRepository,
        private readonly FamilyContract $familyService,
        private readonly AgencyRepository $agencyRepository,
        private readonly ShippingAgencyRepositoryInterface $shippingAgencyRepository,
        private readonly AgencySalaryRepository $agencySalaryRepository,
        private readonly RoomVisitorRepositoryContract $roomVisitorRepository,
        private readonly BdRepository $bdRepository,
        private readonly CpRepositoryContract $cpRepository,
        private readonly WareRepository $wareRepository,
        private readonly UserRepository $userRepository,
        private readonly PackRepository $packRepository,
        private readonly FollowRepository $followRepository,
        private readonly BlackListRepository $blackListRepository,

    ) {}

    public function searchUsers($key, $family)
    {
        $perPage = 10;
        $currentPage = request()->has('page') ? request()->page : 1;

        return $this->userRepository->search($key, $family, $perPage, $currentPage);
    }

    public function searchUsersWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchWithPage($key, $page, $perPage);
    }

    public function searchOwnerRoomWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchOwnerRoomWithPage($key, $page, $perPage);
    }

    public function searchUsersAudioWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchAudioOwnerWithPage($key, $page, $perPage);
    }

    public function searchUsersLiveWithPage($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchLiveOwnerWithPage($key, $page, $perPage);
    }

    public function searchUsersWithPageNew($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchWithPageNew($key, $page, $perPage);
    }

    public function searchUsersInAgency($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchUserAgency($key, $page, $perPage);
    }


    public function bdCountryUsers($key, $page, $country_id)
    {
        $perPage = 10;
        return $this->userRepository->bdCountryUsers($key, $page, $perPage, $country_id);
    }



    public function user_bd($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->user_bd($key, $page, $perPage);
    }

    public function user_bd2($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->user_bd2($key, $page, $perPage);
    }

    public function userBdByCountries($areaManagerId, $key, $page)
    {
        $perPage = 10;
        return $this->userRepository->userBdByCountries($areaManagerId, $key, $page, $perPage);
    }

    public function superAdminUsers($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->superAdminUsers($key, $page, $perPage);
    }


    public function subSuperAdminUsers($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->supSuperAdminUsers($key, $page, $perPage);
    }

    public function subAreaManager($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->subAreaManager($key, $page, $perPage);
    }

    public function superAdminUsers2($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->superAdminUsers2($key, $page, $perPage);
    }


    public function usersAreaManager($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->usersAreaManager($key, $page, $perPage);
    }

    public function usersByCountry($superAdminId, $key, $page)
    {
        $perPage = 10;

        return $this->userRepository->usersByCountry($superAdminId, $key, $page, $perPage);
    }

    public function usersByCountries($areaManager, $key, $page)
    {
        $perPage = 10;

        return $this->userRepository->usersByCountries($areaManager, $key, $page, $perPage);
    }

    public function searchInAgency($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchInAgency($key, $page, $perPage);
    }

    public function superAdminAgencies($key, $page, $countryId)
    {
        $perPage = 10;
        return $this->userRepository->superAdminAgencies($key, $page, $perPage, $countryId);
    }

    public function searchInHostAgency($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchInHostAgency($key, $page, $perPage);
    }

    public function searchUsersInAgencyShipping($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchUserAgencyShipping($key, $page, $perPage);
    }

    public function searchUsersInFamily($key, $page)
    {
        $perPage = 10;
        return $this->userRepository->searchUserFamily($key, $page, $perPage);
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

    public function processUserData($user, $deviceToken, $lat, $long, $iso)
    {
        $countryId = $user->country_id;

        if (!$countryId) {
            if ($iso) {
                $country = Country::where('iso', strtoupper($iso))->first();
                if ($country) {
                    $countryId = $country->id;
                }
            }
        }

        $this->userRepository->updateDeviceToken($user, $deviceToken);

        $currentTime = time();
        // $this->dailyPrizeService->reset(Carbon::createFromTimestamp($user->real_online_time), $user->id);

        $this->userRepository->updateOnlineTime($user, $currentTime);
        // update location
        if (is_numeric($lat) && $lat >= -90 && $lat <= 90 && is_numeric($long) && $long >= -180 && $long <= 180) {
            $this->userRepository->updateLocation($user->id, $lat, $long);
            //            if (!$countryId) {
            //                $countryId = getCountryIdFromLatLong($lat, $long, false);
            //            }
        }
        // end update location

        if ($countryId) {
            $this->userRepository->updateCountry($user, $countryId);
        }
        //        $this->updateCountryAgencyAndBD($user->id, $countryId);

        return $this->userRepository->getUserWithMedals($user->id);
    }

//    public function updateCountryAgencyAndBD($userId, $countryId)
//    {
//        $agency = $this->agencyRepository->findByOwner($userId);
//        if ($agency) {
//            $agency->country_id = $countryId;
//            $agency->save();
//        }
//        $shippingAgency = $this->shippingAgencyRepository->findAgencyByOwnerId($userId);
//        if ($shippingAgency) {
//            $shippingAgency->country_id = $countryId;
//            $shippingAgency->save();
//        }
//        $bd = $this->bdRepository->findByAppUser($userId);
//        if ($bd) {
//            $bd->country_id = $countryId;
//            $bd->save();
//        }
//    }

    public function update_user_multi_images($user, $id, $src)
    {



        return $this->userRepository->update_user_multi_images($user, $id, $src);
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

    public function handleUserRelations($user, $type, $keyword)
    {
        switch ($type) {
            case '1':
            case '2':
            case '3':
            case '6':
                (new UserCounterServices)->UpgradeDateForType($user, 'friend');
                return Common::apiResponse(true, '', $this->getData2($user, $type, $keyword), 200);

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

            $this->typeRoomChat($userId, $followedUserId);

            $this->followRepository->createFollow([
                'user_id' => $userId,
                'followed_user_id' => $followedUserId,
                'status' => 1
            ]);
            $this->handleFollowBack($request->user(), $receiver);
        } else {
            $this->followRepository->updateFollowStatus($follow, 1);
        }

        UserFollowHelper::updateCounts($request->user());
        UserFollowHelper::updateCounts($receiver);

        return Common::apiResponse(true, 'follow done', null, 201);
    }

    public function unFollowUser($request)
    {
        $auth = $request->user();
        $unFollower = $this->userRepository->findOrFail($request->user_id);
        $checkFollowing = $this->followRepository->checkFollowing($auth->id, $unFollower->id);
        $checkFollower = $this->followRepository->checkFollower($auth->id, $unFollower->id);
        if ($checkFollowing && $checkFollower) { // friend
            $unFollowStatus = [
                'friend' => $unFollower->friend - 1,
                'follower' => $unFollower->follower - 1,
                'following' => $unFollower->following - 1,
            ];
            $userStatus = [
                'friend' => $auth->friend - 1,
                'following' => $auth->following - 1,
                'follower' => $auth->follower - 1,
            ];
        } else {
            $unFollowStatus = [
                'follower' => $unFollower->follower - 1,
            ];

            $userStatus = [
                'following' => $auth->following - 1,
            ];
        }
        $this->userRepository->update($unFollowStatus, $unFollower->id);
        $this->userRepository->update($userStatus, $auth->id);

        UserFollowHelper::updateCounts($request->user());
        UserFollowHelper::updateCounts($unFollower);

        $this->followRepository->deleteFollow($auth->id, $unFollower->id);
        return Common::apiResponse(true, 'unFollow done', null, 201);
    }

    protected function handleFollowBack($user, $receiver)
    {
        if ($user->followBack($receiver)) {
            $receiverStatus = [
                'friend' => $receiver->friend + 1,
                'follower' => $receiver->follower + 1,
            ];
            $userStatus = [
                'friend' => $receiver->friend + 1,
                'following' => $receiver->following + 1,
            ];
            CustomNotification::followBack($receiver, $user);
            (new UserCounterServices)->UpgradeDateForType($receiver, 'friend');
            (new UserCounterServices)->eventUser($receiver, 'friend', 1);
        } else {

            $receiverStatus = [
                'follower' => $receiver->follower + 1,
            ];
            $userStatus = [
                'following' => $receiver->following + 1,
            ];
            CustomNotification::follow($receiver, $user);
            (new UserCounterServices)->UpgradeDateForType($receiver, 'followeds');
            (new UserCounterServices)->eventUser($receiver, 'follow', 1);
        }
        $this->userRepository->update($receiverStatus, $receiver->id);
        $this->userRepository->update($userStatus, $user->id);
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
        } elseif ($type == 6) {
            // uses that follow you not friend with you
            $users = $this->followRepository->getFollow($userId);
        } else {
            $users = collect([]);
        }


        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $users);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($users);
    }


    public function getData2(User $user, $type = 1, $keyword = '')
    {
        $userId = $user->id;

        $with = [
            //            'room' => function ($query) {
            //                return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
            //            },
            'followPacks',
            'profile:id,user_id,avatar',
            'ware',
            'UserVip',
            //            'manager',
            'packs',
            //            'country',
            'color_image',
            'followedByAuthUser',
            'followerByAuthUser',
            //            'eligiblePacks',
            //            'friends',
            'chatRoomsAsUser' => function ($q) use ($userId) {
                $q->where('user_id2', $userId)
                    ->withCount(['messages as unread_messages_count' => function ($query) use ($userId) {
                        $query->where('user_id', '<>', $userId)
                            ->where('status', '<>', 'seen');
                    }]);
            },
            'chatRoomsAsUser2' => function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->withCount(['messages as unread_messages_count' => function ($query) use ($userId) {
                        $query->where('user_id', '<>', $userId)
                            ->where('status', '<>', 'seen');
                    }]);
            },
        ];

        if ($type == 1) {
            $users = $this->followRepository->getFollowing($user, $with, $keyword);
        } elseif ($type == 2) {
            $users = $this->followRepository->getFollowers($user, $with, $keyword);
        } elseif ($type == 3) {
            $users = $this->followRepository->getFriends($user, $with, $keyword);
        } elseif ($type == 6) {
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
        //        return $this->vipRepository->getByLevels($levelsList, $type);
        return $this->vipRepository->getByLevelsV2($levelsList, $type);
    }

    public function myStore($user, $request)
    {
        $cacheKey = 'cache-data-mystore-' . $user->id;
        if (\Cache::add($cacheKey, true, now()->addSeconds(30))) {

            if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
                $app_feature = Cache::get('host_agency');
                if ($app_feature) {
                    $targetService = new FixedTargetService($user);
                    $targetService->calculateTarget();
                }
            }
            if ($user->ownerRoom != null) {
                $roomTarget = app(RoomGameContract::class);
                $roomTarget->CalculateRoomSalaries($user->ownerRoom);
            }
        }

        if ($user->device_token  != $request->header('X-Device-Token')) {
            $user->enableSaving = true;
            $user->device_token = $request->header('X-Device-Token');
            $user->save();
        }

        return $user;
    }

    public function showUserCheck(int $userId, bool $isVisit)
    {
        if (!$this->userRepository->existsById($userId)) {
            throw new Exception('User not founded');
        }

        $authId = \Auth::id();

        if ($this->blackListRepository->exists($authId, $userId)) {
            throw new Exception('User is in blacklist');
        }

        if ($this->shouldRecordVisit($authId, $userId, $isVisit)) {
            $this->recordVisit($authId, $userId);
        }
    }

    public function showUser($userId)
    {
        return  $this->userRepository->getUserWithMedals($userId);
        //        $this->packRepository->deleteAllExpiredPacks();
    }

    public function showUsers($usersIds)
    {
        return  $this->userRepository->getUsersWithMedals($usersIds);
        //        $this->packRepository->deleteAllExpiredPacks();
    }

    private function getUserWithRelations($userId)
    {
        // return  $this->userRepository->findUserData($userId);
        return  $this->userRepository->getUserWithMedals($userId);
    }



    private function ensureUserIsAccessible($user, $auth): void
    {
        if (!$user) {
            throw new Exception('User not found');
        }

        if (in_array($user->id, $user?->blacklists->pluck('from_uid')->toArray() ?? [])) {
            throw new Exception('User is in blacklist');
        }
    }

    private function shouldRecordVisit($authId, $userId, bool $isVisit): bool
    {
        return $authId !== $userId && $isVisit;
    }

    private function recordVisit(int $authId, int $userId): void
    {
        $previousVisit = $this->ProfileVisitorRepository->checkVisit($authId, $userId);

        if (!$previousVisit) {

            dispatch(new UserVisitJob($authId, $userId));
        }
    }


    public function vTwoshowUser($userId, $auth, $request, $isVisit)
    {
        $user = $this->userRepository->findOrFail($userId, ['packs' /* => function ($q) {
            $q->whereIn('type', [20, 18, 17, 20, 19, 16, 13, 3, 4, 5])->where('is_used', 1)->with('ware');
        } */, 'profile', 'myroom', 'room.backgroundImage', 'room.background', 'family', 'UserVip.OVip', 'userVips', 'eligiblePacks.ware']);
        if (!$user) throw new \Exception('not found');
        if (in_array($user->id, Common::getUserBlackList($auth->id))) throw new \Exception('in black list');
        $request['user_id'] = $userId;

        if ($auth->id != $user->id && $isVisit == true) {
            //            if (!Common::checkPackPrev($auth->id, 19)) {
            if (Common::checkUserPacks($user['packs'], 19)->isEmpty()) {
                $previousVisit = $this->ProfileVisitorRepository->checkVisit($auth->id, $user->id);
                $user->profileVisits()->syncWithoutDetaching(
                    [
                        $auth->id => [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]
                );

                if (!$previousVisit) {
                    CustomNotification::visitProfile($user, $auth);
                    (new UserCounterServices)->eventUser($user, 'visit-profile');
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
        $Room = Room::where('uid', $room_uid)->where('type', 'audio')->first();
        $query = GiftLog::query()->where('room_id', $Room->id);

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
        $achivement      = app(UserAchievementContract::class);

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
            $achievements = $achivement->getUserAchievement($user);
            $v->data_achivement = class_exists(UserAchievementLevelsResource::class)
                ? UserAchievementLevelsResource::collection($achievements)
                : $achievements;

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
        if (!$whatsappWebhookValidate) throw new \Exception(__('current phone not verified'));

        $user = User::query()->where('phone', $phone)->first();

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
            //            $this->userSettingRepository->updateKey($setting, !$setting->$key);
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

    public function getUserBlackList($userId)
    {
        return $this->blackListRepository->getUserBlackList($userId);
    }

    public function removeUserFromBlackList($userId, $fromUserId)
    {
        return $this->blackListRepository->removeUserFromBlackList($userId, $fromUserId);
    }

    public function addUserToBlackList($userId, $fromUserId)
    {
        DB::beginTransaction();

        try {
            $this->blackListRepository->addUserToBlackList($userId, $fromUserId);

            $this->followRepository->deleteFollow($userId, $fromUserId);
            $this->followRepository->deleteFollow($fromUserId, $userId);

            DB::commit();

            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }


    public function userCharge()
    {
        return $this->userRepository->userCharge();
    }

    public function userStatic($user,  $month, $year)
    {
        $totalSalary = $this->userSalaryRepository->TotalSalary($user->id, $month, $year);
        $user_sallary = $this->userSalaryRepository->getByUser($user->id, $month, $year, 1);

        $total_usd = 0;
        $current_total_hour = "0 / 0";
        $current_total_day = "0 / 0";
        $current_diamond = "0 / 0";

        if ($user_sallary != null) {
            $total_usd          = $totalSalary;
            $current_total_hour = $user_sallary->hours;
            $current_total_day  = $user_sallary->days;
            $diamonds            = $user_sallary->diamond;
            $current_diamond    = $diamonds ?? $current_diamond;
            if ($diamonds) {
                $stringWithoutSpaces = str_replace(' ', '', $diamonds);
                $parts = explode('/', $stringWithoutSpaces);

                // Convert the parts to integers
                $firstNumber = intval($parts[0]);
                $nextTarget = $this->targetRepository->getByDiamonds($firstNumber);
                if ($nextTarget) {
                    $current_diamond = $firstNumber . ' / ' . $nextTarget->diamonds;
                }
            }
        }
        return    $data = [
            "total_diamond" => $user->total_diamond_received,
            "total_usd" => floor($total_usd),
            "current_total_hour" => $current_total_hour,
            "current_total_day" => $current_total_day,
            "diamond" => $current_diamond,
        ];
    }


    protected function typeRoomChat($user_id, $user_id2)
    {
        if (! PackageHelper::isInstalled('chat')) {
            return 0;
        }

        $updateType = ChatRoom::where(function ($q) use ($user_id, $user_id2) {
            $q->where('user_id', $user_id)
                ->where('user_id2', $user_id2);
        })
            ->orWhere(function ($q) use ($user_id, $user_id2) {
                $q->where('user_id', $user_id2)
                    ->where('user_id2', $user_id);
            })
            ->update(['type' => 'friends']);

        return $updateType;
    }

    public function trashedAccount($perPage, $Page, $search, $id)
    {
        return $this->userRepository->trashedUserAccountList($perPage, $Page, $search, $id);
    }

    public function restoreAccount($id)
    {
        return $this->userRepository->restoreAccount($id);
    }

    public function delete($id)
    {
        return $this->userRepository->softDelete($id);
    }

    public function userLevel($perPage, $Page, $search)
    {
        return $this->userRepository->userLevel($perPage, $Page, $search);
    }

    public function updateUserLevel($id, $request)
    {
        $user = $this->userRepository->findById($id);

        ChangeLevelHistory::create([
            'user_id' =>  $user->id,
            'admin_id' => 1,
            'old_total_sender_level' => $user->total_sender_level,
            'new_total_sender_level' => $request->total_sender_level,
            'old_total_received_level' =>  $user->total_received_level,
            'new_total_received_level' => $request->total_received_level,

        ]);
        $user->total_sender_level = $request->total_sender_level;
        $user->total_received_level = $request->total_received_level;
        $user->save();
        return true;
    }

    public function levelHistory($perPage, $page)
    {
        return ChangeLevelHistory::with('user', 'admin')->paginate($perPage, ['*'], 'page', $page);
    }

    public function userDeviceToken($perPage, $Page, $deviceToken, $request)
    {
        return $this->userDevicesHistoryRepository->all($perPage, $Page, $deviceToken, $request);
    }

    public function deleteDeviceToken($id)
    {
        $deviceToken = $this->userDevicesHistoryRepository->findOrFail($id);
        $deviceToken->delete();
        return true;
    }

    public function usersTargets($perPage, $Page)
    {
        return $this->userTargetRepository->all($perPage, $Page);
    }
    public function allUser($perPage, $Page, $familyId, $agencyId, $search, $host)
    {
        return $this->userRepository->all($perPage, $Page, $familyId, $agencyId, $search, $host);
    }

    public function kickAgency($userId)
    {
        $user = $this->userRepository->findOrFail($userId);
        if (UserHandling::checkIfUserOwnerOfAgency($user)) throw new Exception(__('This User is the host Of agency can\'t delete it'));


        UserHandling::kickUserFromAgency($user, 1);
        return true;
    }

    public function kickFamily($userId)
    {
        if (UserHandling::checkIfUserOwnerOfFamily($userId)) throw new Exception(__('This User is the host Of family can\'t delete it go to remove family first'));
        $data = [
            'family_id' => null,
        ];
        $user = $this->userRepository->update($data, $userId);
        $this->familyService->kickFamily($userId);
        return true;
    }

    public function changeAgency($request)
    {
        $agencyOwner = $this->agencyRepository->getAgencyByOwnerId($request->user_id);
        if ($agencyOwner) throw new Exception(__('This User is the host Of agency can\'t delete it'));
        $data = [
            'agency_id' => $request->agency_id,
        ];
        $user = $this->userRepository->update($data, $request->user_id);
        $userSalary = $this->userSalaryRepository->findByUser($request->user_id);
        if ($userSalary) {
            $userSalary->user_agency_id = $request->agency_id;
            $userSalary->save();
        }
        return true;
    }

    public function updateSwitch($request)
    {
        $data = [
            $request->key => $request->value
        ];
        $this->userRepository->update($data, $request->user_id);

        return true;
    }

    public function transferSalary($request)
    {
        $data = [
            'transfer_salary' => $request->transfer_salary,
        ];
        $this->userRepository->update($data, $request->user_id);

        return true;
    }

    public function updateUserSetting($request)
    {
        $user = $this->userRepository->findOrFail($request->user_id);
        $data = [
            $request['key'] => $request['value'],
        ];
        $user->userSetting()->update($data);

        return true;
    }



    public function create($request)
    {
        $data = [
            'uuid' => $request->uuid,
            'name' => $request->name,
            'nickname' => $request->nickname,
            'charge_status' => $request->charge_status,
            'transfer_salary' => $request->transfer_salary,
            'can_play' => $request->can_play,
            'country_id' => $request->country_id,
            'di' => $request->di,
            'user_diamond' => $request->user_diamond,
            'sender_level' => $request->total_sender_level,
            'received_level' => $request->total_received_level,
            'salary' => $request->salary,
            'email' => $request->email,
            'phone' => $request->phone,
            'facebook_id' => $request->facebook_id,
            'google_id' => $request->google_id,
            'huawei_id' => $request->huawei_id,
            'status' => $request->status,
            'type_user' => $request->type_user,
            'manger_type_id' => $request->manger_type_id,

        ];
        $user =  $this->userRepository->create($data);

        if ($request->hasFile('avatar')) {
            $avatar = Common::upload('profile', $request->file('avatar'));
        }
        if ($request->hasFile('image_id')) {
            $image_id = Common::upload('profile', $request->file('image_id'));
        }

        $user->profile->update([
            'avatar' => $avatar ?? '',
            'image_id' => $image_id ?? '',
            'gender' => $request->gender,
        ]);
        //$this->profileRepository->create($profileData);

        $dataUserSitting = [
            'show_invite_code' => $request->show_invite_code,
            'hide_chat' => $request->hide_chat,
            'user_id' => $user->id,
        ];
        $this->userSettingRepository->create($dataUserSitting);

        return true;
    }

    public function showDataUser($id)
    {
        return  $this->userRepository->findOrFail($id, ['userSetting', 'profile', 'packs', 'haveVip']);
    }

    public function update($userId, $request)
    {
        $data = [
            'uuid' => $request->uuid,
            'name' => $request->name,
            'charge_status' => $request->charge_status,
            'transfer_salary' => $request->transfer_salary,
            'can_play' => $request->can_play,
            'country_id' => $request->country_id,
            'user_diamond' => $request->user_diamond,
            'total_sender_level' => $request->total_sender_level,
            'total_received_level' => $request->total_received_level,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status,
            'type_user' => $request->type_user,
            'manger_type_id' => $request->manger_type_id,

        ];
        $user =  $this->userRepository->findOrFail($userId);
        $this->userRepository->update($data, $user->id);

        $profileData = [
            'gender' => $request->gender,
        ];
        if ($request->hasFile('avatar')) {
            $profileData['avatar'] = Common::upload('profile', $request->file('avatar'));
        }
        if ($request->hasFile('image_id')) {
            $profileData['image_id'] = Common::upload('profile', $request->file('image_id'));
        }
        $user->profile->update($profileData);
        $dataUserSitting = [
            'show_invite_code' => $request->show_invite_code,
            'hide_chat' => $request->hide_chat,
        ];
        $user->userSetting->update($dataUserSitting);

        return true;
    }

    public function allCods($id, $perPage, $page)
    {
        return Code::when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->paginate($perPage, ['*'], 'page', $page);;
    }

    public function userSalary($userId, $month, $year,)
    {
        $salary = $this->userSalaryRepository->userSalary($userId, $month, $year);
        $agency = $this->agencyRepository->findAgencyByOwnerId($userId);
        if ($agency) $agencySalary = $this->agencySalaryRepository->agencySalary($agency->id, $month, $year);

        return  ['user_salary' => $salary ?? [], 'agency_Salary' => $agencySalary ?? []];
    }

    public function userPacksAndVip($id)
    {
        return $this->userRepository->findOrFail($id, ['packsUser', 'userHaveVip']);
    }

    public function VisitRoom($id)
    {
        return $this->roomVisitorRepository->getByUser($id);
    }

    public function allUserCp($userId)
    {
        return $this->cpRepository->getByUser($userId);
    }

    public function updateGame($userId)
    {
        $this->userRepository->update(['game_id' => null], $userId);
    }

    public function userPacks($type, $userId, $perPage, $page)
    {
        return $this->packRepository->userPacks($type, $userId, $perPage, $page);
    }


    public function allUsersPlayGame()
    {
        return $this->userRepository->allUsersPlay();
    }

    public function online()
    {
        return $this->userRepository->online();
    }

    public function friends(): LengthAwarePaginator
    {
        return $this->userRepository->friends();
    }

    public function sendPack($user, $request)
    {
        $pack = $this->packRepository->UnusedPack($request->pack_id, $user->id);
        if (!$pack) return Common::apiResponse(0, 'item not found or expired', null, 404);
        $ware = $this->wareRepository->findOrFail($pack->target_id);
        if (!$ware) return Common::apiResponse(0, 'product not found', null, 404);
        $to = User::query()->searchByUuid($request->touid)->first();
        if (!$to) return Common::apiResponse(0, 'user not found', null, 404);
        $pack->user_id   = $to->id;
        $pack->sender_id = $user->id;
        $pack->receive_type = 'send-ware';
        $pack->save();
        CustomNotification::mallSend($user, $to, $pack->type, $ware->show_img);
        return Common::apiResponse(1, 'sent successfully');
    }


    public function userChargeLevel($user)
    {
        $expLevel     = $user->total_charge_coins + $user->sub_charger_coins;
        $currentLevel = $this->vipRepository->findByLevel($user->charge_level, 5);

        if ($currentLevel) {
            $secondLevel = $this->vipRepository->nextLevel($currentLevel->level, 5);
        } else {
            $secondLevel = $this->vipRepository->findByType(5);
        }


        if ($secondLevel != null && $currentLevel != null) {
            $remaining       = $secondLevel?->exp - $user->total_charge_coins;
            $exactlyValue    = @$secondLevel?->exp;
            $progressCurrent = $expLevel - $currentLevel->exp;
            $progressNext    = $secondLevel->exp - $currentLevel->exp;

            $prog = $progressNext != 0 ? ($progressCurrent / $progressNext) : 0;

            if ($prog >= 1) {
                $bar = 1;
            } else {
                $bar = round($prog, 1);
            }
            $progress = $exactlyValue == 0 ? 1 : $bar;
        } elseif ($currentLevel != null) {
            // $remaining       = $secondLevel?->exp == null ? 0 : $secondLevel?->exp - $user?->total_charge_coins;
            $exactlyValue    = $secondLevel?->exp ?? 0;
            $progressCurrent = $expLevel - $currentLevel?->exp ?? 0;
            $progressNext    = @$secondLevel?->exp - $currentLevel?->exp ?? 0;
            // $prog = ($progressCurrent / $progressNext);
            // if ($prog >= 1) {
            //     $bar = 1;
            // } else {
            //     $bar = round($prog, 1);
            // }

            // $progress = $exactlyValue == 0 ? 1 : $bar;
            $progress  = 1;
            $remaining = 0;
        } else {
            $progress  = 1;
            $remaining = 0;
        }
        $expPercentages  = Config::get('exp_percentages') ??  cache('exp_percentages');;
        $chargeLevel = [
            'current_level' => $currentLevel->level ?? 0,
            'current_exp'   => $currentLevel->exp ?? 0,
            'current_img'   => $currentLevel->img ?? '',
            'next_level'    => @$secondLevel ? @$secondLevel->level : ($currentLevel->level ?? 0),
            'next_exp'      => @$secondLevel ?  @$secondLevel->exp ?? 0 : ($currentLevel->exp ?? 0),
            'next_img'      => @$secondLevel ? @$secondLevel->img ?? '' : $currentLevel->img ?? '',
            'remaining'     => @$remaining ?? 0,
            'progress'      => (int)(@$progress ?? 0),
            'exp_charge' =>  $expPercentages['exp_charge_percentage'] ?? 1,
        ];

        return  [
            'gift_level' => Common::level_center($user->id),
            'charge_level' => $chargeLevel,
        ];
    }

    public function dataUser($userId)
    {
        return $this->userRepository->findOrFail($userId, [
            'family',
            'receiverLevel',
            'senderLevel',
            'medals.achievementLevel.achievement'
        ]);
    }

    public function syncBDUsers()
    {
        $bdUsers = User::where('is_bd', 1)->get();

        foreach ($bdUsers as $user) {
            $exists = DB::table('admin_users')
                ->where('app_id', $user->id)
                ->exists();

            if (! $exists) {

                $user->is_bd = 0;
                $user->save();
            }
        }

        return ['message' => 'Done'];
    }


    public function getUserStats($id)
    {
        return $this->userRepository->getStats($id);
    }

    public function getUserRooms($id)
    {
        return $this->userRepository->getRoomsData($id);
    }

    public function getUserVipLevel($id)
    {
        return $this->userRepository->getVipLevelData($id);
    }

    public function getUserFrames($id)
    {
        return $this->userRepository->getFramesData($id);
    }

    public function getEarningsForParent(int $parentId)
    {
        return InvitationEarningResource::collection($this->userRepository->getByParentId($parentId));
    }

    public function claimEarning(int $parentId, int $earningId)
    {
        return DB::transaction(function () use ($parentId, $earningId) {
            $earning = $this->userRepository->claimEarning($earningId);

            if (!$earning || $earning->parent_id !== $parentId) {
                return null;
            }

            $parent = User::find($parentId);
            $amountBefore =  Common::getCurrentBalance($parent->id);

            $parent->increment('di', $earning->amount);

            $parent->increment('di', $earning->amount);

            UserCoinLogHelper::logByType(
                $parent->id,
                $earning->amount,
                $amountBefore,
                UserCoinLogType::INVITATION_CODE,
            );


            return $earning->refresh();
        });
    }
}
