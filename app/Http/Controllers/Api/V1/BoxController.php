<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\Box;
use App\Models\Room;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\GiftLog;
use App\Jobs\OpenBoxJob;
use App\Models\CoreWallet;
use App\Models\PickBoxList;
use App\Models\UserBoxGift;
use Illuminate\Http\Request;
use App\Facades\RedisService;
use App\Jobs\SuperLuckyBoxJob;
use App\Jobs\NormalLuckyBoxJop;
use App\Tik\Services\BoxService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Http\Services\LuckyBoxServices;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Http\Resources\Api\V1\BoxResource;
use App\Http\Resources\Api\V1\BoxUseResource;

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
        $timezone = Common::timeZone();
        $timestamp = Carbon::now($timezone)->timestamp;


        if (!$request->box_id || !$request->room_uid) return Common::apiResponse(0, 'missing params', null, 422);
        $room = Room::query()->where('uid', $request->room_uid)->first();
        if (!$room)  return Common::apiResponse(0, 'not found', null, 404);
        $box = Box::query()->find($request->box_id);
        if (!$box) return Common::apiResponse(0, 'not found', null, 404);
        if (($box->type == 0) && !$request->users_num) return Common::apiResponse(0, 'missing number of users', null, 422);
        if ($user->di < $box->coins)  return Common::apiResponse(0, 'low balance', null, 407);

        $userBoxes =   BoxUse::where('end_at', '>=', $timestamp)->where('user_id', $user->id)->exists();
        if ($userBoxes) return Common::apiResponse(0, 'you send box ', null, 422);
        $label = '';

        if ($request->label && $box->type == 1 && $box->has_label == 1) {
            $label = $request->label;
        }

     return   $this->boxService->sendBox($request, $user, $box, $room, $timezone, $label);
    }


    public function pick3(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        if (!$request->bid) return Common::apiResponse(0, 'missing params', null, 422);

        $keyBoxUse  = 'BoxUse_' . $request->bid;
        $box_use = RedisService::getUnSerialize($keyBoxUse);

        if (!$box_use || $box_use['not_used_num'] == 0 || $box_use['unused_coins'] == 0) {
            return Common::apiResponse(0, __("api.box_not_found"), null, 404);
        }
        if ($box_use['users_num'] != $box_use['used_num']) {

            if (($box_use['not_used_num'] == 1)) {
                $box_use['not_used_num'] = 0;
                $fin = 1;
            } else {
                $fin = 0;
                $box_use['not_used_num'] -= 1;
            }

            $giftService = new LuckyBoxServices();
            $coins = $giftService->getCoins($fin, $box_use['unused_coins'], $box_use['not_used_num']);

            //            put user in redis
            $data = [
                'box_uses_id' => $request->bid,
                'user_id' => $userId,
                'coins' => $coins,
                'room_uid' => $box_use['room_uid'],
                'room_id' => $box_use['room_id'],
                'type' => $box_use['type'],
                'box_uses_owner_id' => $box_use['user_id'],
                'image' => $box_use['image'],
                'label' => $box_use['label']
            ];
            /*$key  = 'LuckyBox_' . $request->bid . '_' . $data['user_id'] . '_' . $data['room_id'] . '_' . $data['type'];
            $retrievedData = RedisService::getUnSerialize($key);*/
            //
            if (UserBoxGift::where(['user_id' => $userId, 'box_uses_id' => true])->exists()) {
                return Common::apiResponse(0, 'used it before', null, 403);
            }
            //
            //            RedisService::updateUnSerialize($key, $data);

            UserBoxGift::query()->create($data);

            $box_use['used_coins'] += $coins;
            $box_use['used_num'] += 1;
            $box_use['unused_coins'] -= $coins;
            //update box use in redis
            RedisService::updateUnSerialize($keyBoxUse, $box_use);
            dispatch(new OpenBoxJob($request->bid, $userId, $user->name))->onQueue('luckyBox');

            /*if ($fin == 1 ){
                dispatch(new OpenBoxJob($request->bid, $userId, $user->name))->onQueue('luckyBox');
            }*/


            User::query()->find($userId)->increment('di', $coins);

            return Common::apiResponse(1, 'لقد حصل ال مستخدم علي مكسب', ['is_win' => true, 'coins' => $coins], 200);
        } else {
            return Common::apiResponse(1, 'لم يحصل ال مستخدم علي مكسب', ['is_win' => false, 'coins' => 0], 200);
        }
    }


    public function pickBox(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $timestamp = Carbon::now()->timestamp;

        if (!$request->bid) return Common::apiResponse(0, 'missing params', null, 422);

        $keyBoxUse  = 'BoxUse_' . $request->bid;
        $box_use = RedisService::getUnSerialize($keyBoxUse);

        if (!$box_use || $box_use['not_used_num'] == 0 || $box_use['unused_coins'] == 0) {
            return Common::apiResponse(0, __("api.box_not_found"), null, 404);
        }

        if ($box_use['end_at'] < $timestamp) {
            return Common::apiResponse(0, __("box closed"), null, 404);
        }

        $box = Box::where('id', $box_use['box_id'])->first();
        // dd($box_use['box_id'],$box);

        if ($box->type == 0) // normal
        {
            return $this->normalBox($box_use, $keyBoxUse, $user, $request);
        } else {  // super
            return  $this->superBox($box_use, $user, $keyBoxUse);
        }
    }

    public function normalBox($box_use, $keyBoxUse, $user, $request)
    {

        if ($box_use['users_num'] != $box_use['used_num']) {

            if (($box_use['not_used_num'] == 1)) {
                $box_use['not_used_num'] = 0;
                $fin = 1;
            } else {
                $fin = 0;
                $box_use['not_used_num'] -= 1;
            }

            $giftService = new LuckyBoxServices();
            $coins = $giftService->getCoins($fin, $box_use['unused_coins'], $box_use['not_used_num']);

            //            put user in redis
            $data = [
                'box_uses_id' => $request->bid,
                'user_id' => $user->id,
                'coins' => $coins,
                'room_uid' => $box_use['room_uid'],
                'room_id' => $box_use['room_id'],
                'type' => $box_use['type'],
                'box_uses_owner_id' => $box_use['user_id'],
                'image' => $box_use['image'],
                'label' => $box_use['label']
            ];

            if (UserBoxGift::where(['user_id' => $user->id, 'box_uses_id' => true])->exists()) {
                return Common::apiResponse(0, 'used it before', null, 403);
            }

            UserBoxGift::query()->create($data);

            $box_use['used_coins'] += $coins;
            $box_use['used_num'] += 1;
            $box_use['unused_coins'] -= $coins;
            //update box use in redis
            RedisService::updateUnSerialize($keyBoxUse, $box_use);
            dispatch(new OpenBoxJob($request->bid, $user->id, $user->name))->onQueue('luckyBox');

            $user->increment('di', $coins);

            return Common::apiResponse(1, 'لقد حصل ال مستخدم علي مكسب', ['is_win' => true, 'coins' => $coins], 200);
        } else {
            return Common::apiResponse(1, 'لم يحصل ال مستخدم علي مكسب', ['is_win' => false, 'coins' => 0], 200);
        }
    }

    public function superBox($box_use, $user, $keyBoxUse)
    {


        if (PickBoxList::where(['box_user_id' => $box_use['id'], 'user_id' => $user->id,])->exists()) {
            return Common::apiResponse(0, 'used it before', null, 403);
        }
        PickBoxList::create([
            'box_user_id' => $box_use['id'],
            'user_id' => $user->id,
        ]);

        $box_use['used_num'] += 1;
        //update box use in redis
        RedisService::updateUnSerialize($keyBoxUse, $box_use);
        return Common::apiResponse(1, ' you are in waiting list', [], 200);
    }
}
