<?php

namespace App\Admin\Controllers;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MyDataResource;
use App\Models\User;
use App\Services\UserService;
use App\Tik\Services\GiftLogService;
use Illuminate\Http\Request;

class GiftLogTestController extends Controller
{

    public function __construct(
        private readonly GiftLogService $giftLogService,
        private readonly UserService $userService,
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
        $user = User::where('id', 303);

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
            $data = $userWithMedals;

        } catch (\Exception $exception) {
            $success = false;
            $message = $exception->getMessage();
            $data = null;
        }

        return view('test.my-data', [
            'success' => $success,
            'message' => $message,
            'user' => $data
        ]);
    }

}
