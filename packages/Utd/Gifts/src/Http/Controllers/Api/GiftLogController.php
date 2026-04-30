<?php

namespace Utd\Gifts\Http\Controllers\Api;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Utd\Gifts\Services\GiftLogService;
use Utd\Gifts\Services\LuckyGiftV2Service;
use Utd\Gifts\Services\UpdateUserWhenSendGift;

class GiftLogController extends Controller
{
    /**
     * Internal cache for resolved services and helpers
     */
    private $instances = [];

    public function __construct() {}

    // Lazy loading via magic method - loads only when accessed
    public function __get($name)
    {
        if (! isset($this->instances[$name])) {
            $this->instances[$name] = match ($name) {
                'giftLogService' => app(GiftLogService::class),
                'Common' => Common::class,
                'LuckyGiftV2Service' => app(LuckyGiftV2Service::class),
                'UpdateUserWhenSendGift' => app(UpdateUserWhenSendGift::class),
                default => null,
            };
        }

        return $this->instances[$name];
    }

    public function gift_queue_cp(Request $request)
    {
        $updateUserWhenSendGift = $this->UpdateUserWhenSendGift;

        $close_open_gifts = settings()->get('close_open_gifts');

        if ($close_open_gifts === 1) {
            return $this->apiResponse(0, __('Send gift stopped by admin'));
        }

        // Update when sending the gift
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'type' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        if (! $this->giftLogService) {
            $this->giftLogService = app(GiftLogService::class);
        }

        try {
            $message = $this->giftLogService->sendGift($request, $updateUserWhenSendGift);
        } catch (Exception $e) {
            return $this->apiResponse(false, $e->getMessage());
        }

        settings()->set('gift_send', true);
        $tpUsers = request()->toUid;
        $idsArray = explode(',', $tpUsers);

        // Optional: convert to integers
        $idsArray = array_map('intval', $idsArray);

        // Prepare API response
        $data = [
            'ids' => $idsArray,
        ];

        return $this->apiResponse(true, $message, $data);
    }

    public function sendLuckyGift2(Request $request)
    {
        $updateUserWhenSendGift = $this->UpdateUserWhenSendGift;

        $stopLucky = settings()->get('stop_luckyGift');
        if ($stopLucky === 1) {
            return $this->apiResponse(0, __('api_responses.try_again'));
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'count' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $data = $request->all();
        $user = $request->user();

        try {
            if ($this->LuckyGiftV2Service) {
                $data = $this->LuckyGiftV2Service->sendLuckyGift2($data, $user, $updateUserWhenSendGift);
            } else {
                throw new Exception(__('api_responses.service_not_found'));
            }
        } catch (Exception $e) {
            return $this->apiResponse(0, $e->getMessage());
        }

        return $this->apiResponse(1, __('api_responses.success'), $data);
    }

    public function sendLuckyGift2V2(Request $request)
    {
        $updateUserWhenSendGift = $this->UpdateUserWhenSendGift;

        $stopLucky = settings()->get('stop_luckyGift');
        if ($stopLucky === 1) {
            return $this->apiResponse(0, __('api_responses.try_again'));
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'owner_id' => 'nullable',
            'toUid' => 'required',
            'num' => 'required|integer|min:1',
            'count' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        $data = $request->all();
        $user = $request->user();

        try {
            if ($this->LuckyGiftV2Service) {
                $data = $this->LuckyGiftV2Service->sendLuckyGift2V2($data, $user, $updateUserWhenSendGift);
            } else {
                throw new Exception(__('api_responses.service_not_found'));
            }
        } catch (Exception $e) {
            return $this->apiResponse(0, $e->getMessage());
        }

        return $this->apiResponse(1, __('api_responses.success'), $data);
    }

    /**
     * Safe API response helper
     */
    private function apiResponse($status, $message, $data = null, $code = 200)
    {
        $commonClass = $this->Common;
        if ($commonClass) {
            return $commonClass::apiResponse($status, $message, $data, $code);
        }

        return response()->json([
            'status' => (bool) $status,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
