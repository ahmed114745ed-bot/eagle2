<?php

namespace App\Services;

use App\Repositories\ProfileRepository;
use App\Helpers\Common;
use App\Services\UserCounterServices;
use App\Http\Requests\Api\V1\Profile\ProfileRequest;
use App\Http\Resources\Api\V1\UserResource as V1UserResource;
use App\Http\Resources\Api\V1\UserVisitorResource;
use App\Repositories\User\UserRepository;
use Modules\Public\Http\Services\UserCounterServices as ServicesUserCounterServices;

class ProfileService
{
    protected $profileRepo;
    protected $userRepository;
    protected $profileRelationService;

    public function __construct(ProfileRepository $profileRepo,UserRepository $userRepository,ProfileRelationService $profileRelationService)
    {
        $this->profileRepo = $profileRepo;
        $this->userRepository = $userRepository;
        $this->profileRelationService = $profileRelationService;
    }

    public function updateProfile(ProfileRequest $request)
    {
        $data = $request->only(['name', 'email', 'phone', 'nickname', 'country_id', 'bio', 'chat_id', 'notification_id']);
        $user = $this->profileRepo->updateUser($request->user(), $data);

        $profileData = $request->only(['gender', 'birthday', 'province', 'city', 'country']);
        $profile = $this->profileRepo->updateProfile($user->profile, $profileData);

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $imageType = $img->getClientOriginalExtension();
            if ($imageType == 'gif' && !Common::hasInPack($user->id, 22, false)) {
                return Common::apiResponse(0, __('api_responses.gifImage'), 404);
            }
            $imagePath = Common::upload('profile', $img);
            $this->profileRepo->updateAvatar($profile, $imagePath);
        }

        $out = new V1UserResource($user);
        if ($profile->avatar === null) {
            $profile->avatar = $profile->gender == 1 ? "custom_image/male.png" : "custom_image/female.png";
            $this->profileRepo->updateAvatar($profile, $profile->avatar);
        }

        if ($profile->wasRecentlyCreated) {
            $title = __('api.welcome', ['name' => $user->name, 'app_name' => env('APP_NAME')], 'en');
            $titleAr = __('api.welcome', ['name' => $user->name, 'app_name' => __(env('APP_NAME'), locale: 'ar')], 'ar');
            Common::sendOfficialMessage($user->id, $title, $user->name, titleAr: $titleAr);
            (new ServicesUserCounterServices)->eventUser($user, 'official-messages');
        }
        return $out;
    }

    public function showProfile($me, $id)
    {
        (new ServicesUserCounterServices)->UpgradeDateForType($me, 'visitor');
        $user = $this->userRepository->findUserById($id);

        if ($user && $me->id !== $user->id && !Common::checkPackPrev($me->id, 19)) {
            $this->userRepository->logProfileVisit($user, $me->id);
        }

        if ($user) {
            return Common::apiResponse(true, '', new V1UserResource($user), 200);
        }
        return Common::apiResponse(false, 'user not found', [], 404);
    }

    public function getProfileVisitorsList($user)
    {
        (new ServicesUserCounterServices)->UpgradeDateForType($user, 'visitor');

        $profileVisitors = $this->profileRepo->getProfileVisits($user);

        [$userFollowers, $senderLevels, $receivedImage] = $this->profileRelationService->getHelperArrays($user, $profileVisitors);

        UserVisitorResource::initializeData($senderLevels, $receivedImage, $userFollowers);

        $visitors = UserVisitorResource::collection($profileVisitors);
        $jsonResponse = Common::apiResponse(1, '', $visitors);

        UserVisitorResource::clear();

        return $jsonResponse;
    }
}