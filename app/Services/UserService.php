<?php

namespace App\Services;

use App\Models\Vip;
use App\Models\User;
use App\Models\Follow;
use App\Helpers\Common;
use App\Facades\UserHandling;
use App\Http\Services\WhatsappOtp;
use App\Facades\CustomNotification;
use App\Repositories\PackRepository;
use App\Http\Services\WhatsappWebhook;
use App\Repositories\FollowRepository;
use App\Repositories\User\UserRepository;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\Api\V1\UserRelationsResource;
use Modules\Public\Http\Services\UserCounterServices;

class UserService
{
    protected $userRepository;
    protected $packRepository;
    protected $followRepository;

    public function __construct(UserRepository $userRepository,PackRepository $packRepository, FollowRepository $followRepository)
    {
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

    public function updateLocation($userId,$lat,$log)
    {
        $this->userRepository->updateLocation($userId,$lat,$log);
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
                (new UserCounterServices)->UpgradeDateForType($user, 'friend');
                return Common::apiResponse(true, '', $this->getData($user, $type), 200);
                
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

    public function unfollowUser($request)
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

        if ($type == 1){
            $data = Follow::query()->where('user_id' , $userId)->whereHas('followed')->with('followed', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('id')->paginate(15);
            $collect = collect($data->items());
            $users    = $collect->pluck('followed');
            // dd($users);


        }elseif ($type == 2){
            $data = Follow::query()->whereHas('follower')->where('followed_user_id' , $userId)->whereHas('follower')->with('follower', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('id')->paginate(15);
            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        } elseif ($type == 3) {
            $data = Follow::query()->whereHas('followed')->whereHas('follower')->join('follows as f1', function (JoinClause $join){
                $join->on('follows.user_id', '=', 'f1.followed_user_id')
                     ->on('f1.user_id', '=','follows.followed_user_id');
            })->where('follows.user_id', $userId)->with('follower', function ($query) {
                $query->with([
                                 'room' => function ($query) {
                                     return $query->withoutAppends()->select(['id', 'room_pass', 'uid']);
                                 }, 'followPacks', 'profile', 'ware', 'UserVip'
                             ]);
            })->orderByDesc('follows.id')->paginate(15);

            $collect = collect($data->items());
            $users    = $collect->pluck('follower');
        }else{
            $users = collect([]);
        }


        [$userFollowers, $vipsSenderImages, $vipsReceivedImages] = $this->getHelperArrays($user, $users);


        UserRelationsResource::initializeData($vipsReceivedImages, $vipsSenderImages, $userFollowers);

        return UserRelationsResource::collection($users);
    }

    public function getHelperArrays(User $user, $data): array
    {
        $userFollowers      = Follow::query()->where('user_id',$user->id)->pluck('followed_user_id')->toArray();


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
        return Vip::query()->whereIn('level', $levelsList)
                  ->where('type', $type)->select('img', 'level')->get();
    }
}
