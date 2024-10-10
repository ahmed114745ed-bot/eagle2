<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Services\ProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ProfileRequest;
use App\Http\Resources\Api\V1\NewProfileResource;
use App\Http\Resources\Api\V1\UserRelationsResource;
use App\Models\User;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // public function users(Request $request)
    // {
    //     $user = Auth::user();
    //     $latitude = $user->lat;
    //     $longitude = $user->long;
    //     $users = User::query()->select('users.*')
    //         ->selectRaw(
    //             "(6371 * acos(cos(radians(?)) * cos(radians(users.lat)) * cos(radians(users.long) - radians(?)) + sin(radians(?)) * sin(radians(users.lat)))) AS distance",
    //             [$latitude, $longitude, $latitude]
    //         )
    //         ->where('users.id', '!=', $user->id)
    //         ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $user->id))
    //         ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $user->id)])
    //         ->orderBy('distance')
    //         ->paginate(10);

    //     return $users;
    //     return Common::apiResponse(true,'', NewProfileResource::collection($users), 200);
    // }

    public function getNearbyUsers($userId, $distance = 10)
    {
        $user = Auth::user();
    
        $latitude = $user->lat;
        $longitude = $user->long;
        $users = User::query()
        ->select(
            'users.*',
            DB::raw("(6371 * acos(cos(radians($latitude)) 
                * cos(radians(users.lat)) 
                * cos(radians(users.long) - radians($longitude)) 
                + sin(radians($latitude)) 
                * sin(radians(users.lat)))) AS distance")
        )
        ->whereNotNull('lat')
        ->whereNotNull('long')
        ->whereDoesntHave('ignores', fn($q) => $q->where("ignore_user_id", $user->id))
        ->withExists(['likedBy' => fn($q) => $q->where("liked_user_id", $user->id)])
        ->where('id', '!=', $userId) 
        ->orderBy('distance', 'asc')
        ->paginate(10); 
        return Common::apiResponse(true,'', NewProfileResource::collection($users), 200);
    }
    

    public function myProfileVisitorsList(Request $request)
    {
        $user = $request->user();
        return $this->profileService->getProfileVisitorsList($user);
    }

    public function related()
    {
        $randomUsers = $this->profileService->getRelatedUsers(20);

        return Common::apiResponse(true,'',  UserRelationsResource::collection($randomUsers), 200);
    }
}

