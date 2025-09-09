<?php

namespace App\Admin\Controllers;

use App\Classes\Gifts\UpdateUserWhenSendGift;
use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Tik\Services\GiftLogService;

class GiftLogTestController extends Controller
{

    public function __construct(private readonly GiftLogService $giftLogService)
    {
    }

    public function showGiftForm()
    {
        return view('gifts-test');
    }

    public function gift_queue_cp_view(\Illuminate\Http\Request $request, UpdateUserWhenSendGift $updateUserWhenSendGift)
    {
        $close_open_gifts = settings()->get('close_open_gifts');

        if ($close_open_gifts == 1) {
            return view('gifts-test', [
                'success' => false,
                'message' => __('Send gift stopped by admin')
            ]);
        }

        try {
            $message = $this->giftLogService->sendTestGift($request, $updateUserWhenSendGift);
        } catch (\Exception $e) {
            return view('gifts-test', [
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        settings()->set('gift_send', true);

        return view('gifts-test', [
            'success' => true,
            'message' => $message
        ]);
    }

}
