<?php

namespace App\Admin\Controllers;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\Common;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Http\Resources\Api\V1\RoomResource;
use App\Http\Resources\Api\V1\UserVisitorResource;
use App\Models\User;
use App\Repositories\FollowRepository;
use App\Services\ProfileService;
use App\Services\UserService;
use App\Tik\Repositories\UserRepository;
use App\Tik\Services\GiftLogService;
use Illuminate\Http\Request;
use Modules\Public\Http\Services\UserCounterServices;

class GiftLogTestController extends Controller
{

    public function __construct(
        private readonly GiftLogService $giftLogService,
        private readonly UserService $userService,
        private readonly ProfileService $profileService,
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
}
