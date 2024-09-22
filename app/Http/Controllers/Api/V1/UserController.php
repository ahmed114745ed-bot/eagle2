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

        $type     = $request->input('type', 2);
        $room_uid = $request->input('room_uid');
        $limit    = $request->input('is_home') ? 3 : 30;
        $user_id  = $request->user()->id; // Assuming you want the authenticated user's ID
        // Define the initial query
        $query = GiftLog::query()->where('roomowner_id', $room_uid);

        if ($type == 1) {
            $query = $query->whereBetween('created_at', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        }
        // else {
        //     $query = $query->whereBetween('created_at', [
        //         Carbon::now()->startOfMonth(),
        //         Carbon::now()->endOfMonth()
        //     ]);
        // }

        // Select the required columns and group by 'sender_id'
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

        $toArray = $data->toArray();


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
