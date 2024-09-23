<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Helpers\Common;
use App\Models\GiftLog;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Services\RoomGameServices;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Resources\Api\V1\MyStoreResource;
use App\Http\Resources\Api\V1\MangerTypeResource;
use Modules\FixedTarget\Services\FixedTargetService;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;

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

        $userWithMedals = $this->userService->processUserData($user, $request->header('device'), $request->header('lat'), $request->header('long'));

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

    public function unFollow(Request $request)
    {
        return $this->userService->unFollowUser($request);
    }

    public function my_store_all(Request $request)
    {
        $user = $request->user();
        $user = $this->userService->myStore($user, $request);
        $data = new MyStoreResource($user);
        return Common::apiResponse(true, '', $data, 200);
    }

    public function ranking_room(Request $request)
    {
        $toArray =  $this->userService->roomRanking($request);
        return Common::apiResponse(1, '', $toArray);
    }

    public function show(Request $request, $id)
    {
        $isVisit = @$request->is_visit == 'true' ? true : false;
        $auth   = $request->user();
        try {
            
            $user = $this->userService->showUser($id, $auth, $request, $isVisit);
        } catch (Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        $data = new UserResource($user);
        return Common::apiResponse(true, '', $data, 200);
    }
}
