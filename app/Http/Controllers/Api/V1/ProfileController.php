<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Services\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ProfileRequest;
use App\Models\User;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected $profileService;
    protected $userService;

    public function __construct(ProfileService $profileService, UserService $userService)
    {
        $this->profileService = $profileService;
        $this->userService = $userService;
    }

    public function update(ProfileRequest $request)
    {
       $out = $this->profileService->updateProfile($request);
 
        return Common::apiResponse(true, 'profile updated successfully', $out, 200);
    }

    public function show(Request $request, $id)
    {
        $me = $request->user();
        return $this->profileService->showProfile($me, $id);
    }

    public function liked(Request $request)
    {
        $userId = Auth::id();
        $likedUserId = $request->user_id;

        $message = $this->userService->toggleLike($userId, $likedUserId);

        return Common::apiResponse(true, $message, 200);
    }
    
    public function ignored(Request $request)
    {
        $userId = Auth::id();
        $ignoreUserId = $request->user_id;

        $message = $this->userService->toggleIgnored($userId, $ignoreUserId);

        return Common::apiResponse(true, $message, 200);
    }
}

