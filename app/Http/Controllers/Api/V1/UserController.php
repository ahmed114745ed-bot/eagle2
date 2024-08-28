<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\MyStoreResource;
use App\Http\Services\RoomGameServices;
use Exception;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\MyDataResource;
use Modules\FixedTarget\Services\FixedTargetService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function search(Request $request)
    {
        $key = $request->search;
        $users = $this->userService->searchUsers($key);

        return response()->json($users);
    }

    public function search2(Request $request)
    {
        $key = $request->q;
        $page = $request->get('page', 1);
        $users = $this->userService->searchUsersWithPage($key, $page);

        return response()->json($users);
    }

    public function userAgency(Request $request)
    {
        $key = $request->q;
        $page = $request->get('page', 1);
        $users = $this->userService->searchUsersInAgency($key, $page);

        return response()->json($users);
    }

    public function joinAccount(Request $request)
    {
        $user = $request->user();
        try {
            $this->userService->bind($user, $request);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        return Common::apiResponse(1, 'bind successful', new UserResource($user));
    }

    public function my_data(Request $request)
    {
        $user = $request->user();
        $user->enableSaving = false;

        $userWithMedals = $this->userService->processUserData($user, $request->header('device'),$request->header('lat'),$request->header('long'));

        $this->userService->unlockDressHand($user->id);

        $data = new MyDataResource($userWithMedals);
        return Common::apiResponse(true, '', $data, 200);
    }

    public function userFriend(Request $request)
    {
        $user = $request->user();
        return $this->userService->handleUserRelations($user, $request->type);
    }

    public function follow(Request $request)
    {
        return $this->userService->followUser($request);
    }

    public function unfollow(Request $request)
    {
        return $this->userService->unfollowUser($request);
    }

    public function my_store_all(Request $request)
    {
        $user = $request->user();
        $cacheKey = 'cache-data-mystore-' . $user->id;
        if (\Cache::add($cacheKey, true, now()->addSeconds(30))) {

            $targetService = new FixedTargetService($user);
            $targetService->calculateTarget();
            if($user->ownerRoom != null){
                $roomTarget = new RoomGameServices();
                $roomTarget->CalculateRoomSalaries($user->ownerRoom);
            }
        }

        if ($user->device_token  != $request->header('device')) {
            $user->enableSaving = true;
            $user->device_token = $request->header('device');
            $user->save();
        }

        /* if(($user->type_user == 2 || $user->type_user == 4 ) && $user->agency){
            $targetService->updateAgencySalaries($user->agency_id);
        } */

        $data = new MyStoreResource($user);
        return Common::apiResponse(true, '', $data, 200);
    }
}
