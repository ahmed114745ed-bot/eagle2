<?php

namespace App\Admin\Controllers;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Resources\Api\V1\RoomAdminsResource;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\Api\V1\RoomSearchResource;
use App\Http\Resources\Api\V1\UserResourceSerche;
use App\Http\Resources\Api\V1\UserResourceSearchV2;
use App\Http\Resources\Api\V1\UserVisitorResource;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Community\SearchRepository;
use App\Services\ProfileService;
use App\Services\UserService;
use App\Tik\Services\GiftLogService;
use App\Tik\Services\RoomRepoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GiftLogTestController extends Controller
{

    public function __construct(
        private readonly GiftLogService $giftLogService,
        private readonly UserService $userService,
        private readonly ProfileService $profileService,
        private readonly RoomRepoService $roomService,
    )
    {
    }

    public function showGiftForm()
    {
        return view('test.gifts-test');
    }

    public function gift_queue_cp_view(\Illuminate\Http\Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $close_open_gifts = settings()->get('close_open_gifts');

        if ($close_open_gifts == 1) {
            return view('test.gifts-test', [
                'success' => false,
                'message' => __('Send gift stopped by admin')
            ]);
        }

        try {
            $message = $this->giftLogService->sendTestGift($request, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return view('test.gifts-test', [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        settings()->set('gift_send', true);

        return view('test.gifts-test', [
            'success' => true,
            'message' => $message
        ]);
    }

    public function showMyDataTest()
    {
        return view('test.my-data');
    }


    public function myDataTest(Request $request)
    {
        $user = User::where('id', 303)->first();


        try {
            $userWithMedals = $this->userService->processUserData(
                $user,
                $request->input('X-Device-Token'),
                $request->input('lat'),
                $request->input('long')
            );
            request()->default_background = \DB::table('backgrounds')
                ->where('enable', 1)
                ->orderBy('id', 'asc')
                ->first()?->img;

            $success = true;
            $message = 'User data processed successfully';

            $data = (new MyDataResource($userWithMedals))
                ->resolve();

        } catch (\Exception $exception) {
            $success = false;
            $message = $exception->getMessage();
            $data = null;
        }

        return view('test.my-data', [
            'success' => $success,
            'message' => $message,
            'user' => $data,
        ]);
    }

    public function showRelations()
    {
        return view('test.relations');
    }

    public function userFriend(Request $request)
    {
        $user = User::whereId(303)->select(['id', 'name'])->first();
        $keyword = $request->keywords ?? '';

        $response = $this->userService->handleUserRelations($user, 3, $keyword);

        $original = $response->getData(true);

        return view('test.relations', [
            'success' => $original['success'] ?? false,
            'message' => $original['message'] ?? '',
            'data'    => $original['data'] ?? [],
        ]);
    }

    public function showVisitors()
    {
        return view('test.visitors');
    }

    public function visitorsList(Request $request)
    {
        $user = User::whereId(303)->select(['id', 'name'])->first();
        $keyword = $request->keywords ?? '';

        [$profileVisitors, $userFollowers, $senderLevels, $receivedImage] = $this->profileService->getProfileVisitorsList($user, $keyword);
        UserVisitorResource::initializeData($senderLevels, $receivedImage, $userFollowers);

        $visitors = UserVisitorResource::collection($profileVisitors);

        UserVisitorResource::clear();

        $original = $visitors->response()->getData(true);

        return view('test.visitors', [
            'success' => true,
            'message' => '',
            'data'    => $original['data'] ?? [],
        ]);
    }

    public function showRoomAdmin()
    {
        return view('test.room_admins');
    }

    public function getAdmins(Request $request)
    {
        $admins = $this->roomService->roomAdmins(1206);

        $data = RoomAdminsResource::collection($admins);

        return view('test.room_admins', [
            'success' => true,
            'message' => '',
            'data'    => $data,
        ]);
    }

    public function showRooms()
    {
        return view('test.rooms');
    }

    public function rooms(Request $request)
    {
        request()->default_background = \DB::table('backgrounds')->where('enable', 1)->orderBy('id', 'asc')->limit(1)->first()->img;
        $rooms = $this->roomService->getAllRooms($request);

        $data = RoomResource::collection($rooms);

        return view('test.rooms', [
            'success' => true,
            'message' => '',
            'data'    => $data,
        ]);
    }

    public function showSearch()
    {
        return view('test.search');
    }

    public function merge_search(Request $request)
    {
        $keywords = 55;
        $user_id = 303;

        if (!$keywords || !$user_id) {
            return Common::apiResponse(0, 'Missing parameters');
        }

        (new SearchRepository())->saveSearchHistory($user_id, $keywords);

        $user = User::with(['blockedUsers:id,from_uid', 'blockedMe:id,user_id'])->find(303);
        $blockedByMe = $user->blockedUsers->pluck('from_uid')->toArray();
        $blockedMe = $user->blockedMe->pluck('user_id')->toArray();
        $blockedUserIds = array_unique(array_merge($blockedByMe, $blockedMe));

        $result = [
            'user' => UserResourceSearchV2::collection($this->userSearchHand($user_id, $keywords, $blockedUserIds)),
            'rooms' => RoomSearchResource::collection($this->searchRooms($user_id, $keywords, $blockedUserIds)),
            ];

        return view('test.search', [
            'success' => true,
            'message' => '',
            'data'    => $result,
        ]);
    }

    public function userSearchHand(int $userId, string $keywords, $blockedUserIds, int $page = 1)
    {
        if (!$userId || !$keywords) {
            return [];
        }

        $users = User::query()
            ->select([
                '*',
                DB::raw("
            CASE
                WHEN special_id = '{$keywords}' THEN 1000
                WHEN special_id LIKE '{$keywords}%' THEN 900 - LENGTH(special_id)
                WHEN uuid = '{$keywords}' THEN 800
                WHEN uuid LIKE '{$keywords}%' THEN 700 - LENGTH(uuid)
                WHEN special_id LIKE '%{$keywords}%' THEN 600
                WHEN uuid LIKE '%{$keywords}%' THEN 500
                ELSE 0
            END AS total_score
        ")
            ])
            ->with([
                'profile:id,user_id,avatar',
                'color_image',
                'packs',
                'eligiblePacks.ware',
                'specialId.ware',
                'ownAgency'
            ])
            ->where(function ($query) use ($keywords) {
                $query->where('special_id', 'like', "%{$keywords}%")
                    ->orWhere('uuid', 'like', "%{$keywords}%");
            })
            ->whereNotIn('id', $blockedUserIds)
            ->where('status', 1)
            ->having('total_score', '>', 0)
            ->orderByDesc('total_score')
            ->take(10)
            ->get();
//            ->paginate(10, ['*'], 'page', $page);

        return $users;
    }

    public function searchRooms(int $userId, string $keywords, $blockedUserIds, int $page = 1): array|Collection
    {
        $user = User::query()
            ->fitterByUuid($keywords)
            ->with(['packs' => function ($q) {
                $q->where('type', 16)
                    ->where('is_used', 1)
                    ->where(function ($q) {
                        $q->where('expire', 0)
                            ->orWhere('expire', '>=', now()->timestamp);
                    });
            }])
            ->addSelect([
                '*',
                DB::raw("((LENGTH(uuid) - LENGTH(REPLACE(uuid, '{$keywords}', ''))) / CHAR_LENGTH(uuid)) * 100 AS matching_percentage")
            ])
            ->orderByDesc('matching_percentage')
            ->first();

        if (!$user || $user?->packs->isNotEmpty()) {
            return [];
        }

        $keywords = $user->id;

        return Room::with([
            'owner',
            'owner.packs'
        ])
            ->whereHas('owner', function ($query) {
                $query->where('status', 1);
            })
            ->where('uid', 'like',  $keywords . '%')
            ->whereNotIn('uid', $blockedUserIds)
            ->orderBy('hot', 'desc')
            ->take(2)
            ->get();
    }
}
