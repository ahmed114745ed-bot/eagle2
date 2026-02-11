<?php

namespace Utd\LuckyBox\Http\Controllers;

use Carbon\Carbon;
use Utd\Room\Entities\Room;
use App\Models\User;
use App\Enums\TypeBox;
use App\Helpers\Common;
use Utd\LuckyBox\Jobs\OpenBoxJob;
use Utd\Room\Entities\RoomVisitor;
use Illuminate\Http\Request;
use App\Facades\RedisService;
use Utd\LuckyBox\Jobs\NormalBoxRtmJob;
use App\Enums\UserCoinLogType;
use App\Helpers\UserCoinLogHelper;
use Utd\LuckyBox\Jobs\TestSuperLuckyBoxJob;
use Utd\LuckyBox\Entities\Box;
use App\Facades\CustomNotification;
use App\Http\Controllers\Controller;
use Utd\LuckyBox\Entities\BoxUse;
use Utd\LuckyBox\Services\BoxService;
use Utd\LuckyBox\Entities\PickBoxList;
use Utd\LuckyBox\Entities\UserBoxGift;
use Utd\LuckyBox\Services\LuckyBoxServices;
use Utd\LuckyBox\Http\Resources\BoxResource;

class BoxController extends Controller
{
    public function __construct(private BoxService $boxService) {}

    public function index()
    {
        $normal = Box::query()->where('type', 0)->orderByDesc('id')->get();
        $super = Box::query()->where('type', 1)->orderByDesc('id')->get();
        $data = [
            'normal' => BoxResource::collection($normal),
            'super' => BoxResource::collection($super)
        ];
        return Common::apiResponse(1, '', $data, 200);
    }

    public function send(Request $request)
    {
        $user = $request->user();

        $roomUid = $request->room_uid;
        $roomId = $request->room_id;

        if (!$request->box_id || (!$roomUid && !$roomId)) {
            return Common::apiResponse(0, 'missing params', null, 422);
        }

        $room = Room::when($roomUid, fn($q) => $q->where('uid', $roomUid))
            ->when($roomId, fn($q) => $q->orWhere('id', $roomId))
            ->first();

        if (!$room) return Common::apiResponse(0, 'room not found', null, 404);
        $box = Box::query()->find($request->box_id);
        if (!$box) return Common::apiResponse(0, 'box not found', null, 404);
        if (($box->type == 0) && !$request->users_num) return Common::apiResponse(0, 'missing number of users', null, 422);
        if ($user->di < $box->coins) return Common::apiResponse(0, __('low balance'), null, 407);

        $label = '';

        if ($request->label && $box->type == 1 && $box->has_label == 1) {
            $label = $request->label;
        }

        return $this->boxService->sendBox($request, $user, $box, $room, $label);
    }

    public function sendTest(Request $request, $user)
    {
        if (!$request->box_id || !$request->room_uid) return Common::apiResponse(0, 'missing params', null, 422);
        $room = Room::query()->where('uid', $request->room_uid)->first();
        if (!$room) return Common::apiResponse(0, 'room not found', null, 404);
        $box = Box::query()->find($request->box_id);
        if (!$box) return Common::apiResponse(0, 'box not found', null, 404);
        if (($box->type == 0) && !$request->users_num) return Common::apiResponse(0, 'missing number of users', null, 422);
        if ($user->di < $box->coins) return Common::apiResponse(0, __('low balance'), null, 407);

        $label = '';

        if ($request->label && $box->type == 1 && $box->has_label == 1) {
            $label = $request->label;
        }

        return $this->boxService->sendBox($request, $user, $box, $room, $label);
    }

    public function testSendSuperBoxes(Request $request)
    {
        dispatch(new TestSuperLuckyBoxJob($request->all()))->onQueue('test-super-lucky-box');
    }

    public function pickBox(Request $request)
    {
        $user = $request->user();
        $timestamp = Carbon::now()->timestamp;

        if (!$request->bid) return Common::apiResponse(0, 'missing params', null, 422);

        $keyBoxUse = 'BoxUse_' . $request->bid;
        $box_use = BoxUse::with('userBoxGifts', 'user', 'box')->find($request->bid);

        if (!$box_use) {
            return Common::apiResponse(0, __("api.box_not_found"), null, 404);
        }
        if ($box_use['end_at'] < $timestamp) {
            return Common::apiResponse(0, __("box closed"), null, 404);
        }

        if ($box_use->box->type == 0) {
            return $this->normalBox($box_use, $user, $request);
        } else {
            return $this->superBox($box_use, $user, $keyBoxUse, $request->bid);
        }
    }

    public function normalBox($box_use, $user, $request)
    {
        if (UserBoxGift::where(['user_id' => $user->id, 'box_uses_id' => $request->bid])->exists()) {
            return Common::apiResponse(0, 'used it before', null, 403);
        }

        if ($box_use->users_num != $box_use->used_num) {
            if (($box_use->not_used_num == 1)) {
                $box_use->not_used_num = 0;
                $fin = 1;
            } else {
                $fin = 0;
                $box_use->not_used_num -= 1;
            }

            $giftService = new LuckyBoxServices();
            $coins = $giftService->getCoins($fin, $box_use->unused_coins, $box_use->not_used_num);

            $data = [
                'box_uses_id' => $request->bid,
                'user_id' => $user->id,
                'coins' => $coins,
                'room_uid' => $box_use->room_uid,
                'room_id' => $box_use->room_id,
                'type' => $box_use->type,
                'box_uses_owner_id' => $box_use->user_id,
                'image' => $box_use->image,
                'label' => $box_use->label,
            ];

            UserBoxGift::query()->create($data);

            $box_use->used_coins += $coins;
            $box_use->used_num += 1;
            $box_use->unused_coins -= $coins;
            $box_use->save();

            $amountBefore = $user->di;
            UserCoinLogHelper::logByType(
                $user->id,
                $coins,
                $amountBefore,
                UserCoinLogType::LUCK_BOX,
            );
            $user->increment('di', $coins);
            $countWinners = $box_use->userBoxGifts()->count();

            if ($box_use->not_used_num == 0) {
                dispatch(new NormalBoxRtmJob($box_use->id))->onQueue('test-super-lucky-box');
            }
            if ($coins > 0) {
                CustomNotification::luckyBox($user, $coins, $box_use->box->image);
            }
            return Common::apiResponse(1, 'لقد حصل ال مستخدم علي مكسب', ['is_win' => true, 'coins' => (int) $coins], 200);
        } else {
            return Common::apiResponse(0, __('The box time has ended'), ['is_win' => false, 'coins' => 0], 403);
        }
    }

    public function superBox($box_use, $user, $keyBoxUse, $bosUserId)
    {
        if (PickBoxList::where('box_user_id', $bosUserId)->where('user_id', $user->id)->exists()) {
            return Common::apiResponse(0, 'used it before', null, 403);
        }

        PickBoxList::create([
            'box_user_id' => $bosUserId,
            'user_id' => $user->id,
        ]);

        $box_use['used_num'] += 1;
        RedisService::updateUnSerialize($keyBoxUse, $box_use);
        return Common::apiResponse(1, 'you are in waiting list', ["type" => strtolower(TypeBox::from($box_use['type'])->name)], 200);
    }
}
