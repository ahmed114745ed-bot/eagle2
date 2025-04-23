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
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Http\Services\LuckyBoxServices;
use App\Jobs\AllOpeningRoomsZegoRequest;
use App\Http\Resources\Api\V1\BoxResource;
use App\Http\Resources\Api\V1\BoxUseResource;

class BoxController extends Controller
{
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
        if (!$request->box_id || !$request->room_uid) return Common::apiResponse(0, 'missing params', null, 422);
        $room = Room::query()->where('uid', $request->room_uid)->first();
        if (!$room)  return Common::apiResponse(0, 'not found', null, 404);
        $box = Box::query()->find($request->box_id);
        if (!$box) return Common::apiResponse(0, 'not found', null, 404);
        if (($box->type == 0) && !$request->users_num) return Common::apiResponse(0, 'missing number of users', null, 422);
        $user = $request->user();
        if ($user->di < $box->coins) {
            return Common::apiResponse(0, 'low balance', null, 407);
        }

        $label = '';


        $app_percentage = Config::query()->where('name', 'app_wallet_lucky_box')->first()?->value ?? 2;
        $walletCoins = ($box->coins * $app_percentage) / 100;
        $boxCoin = $box->coins - $walletCoins;
        $walletApp = CoreWallet::where('name', 'lucky_box')->first();
        $newWalletCoins = $walletApp->coins + $walletCoins;
        $walletApp->update([
            'coins' => $newWalletCoins,
        ]);



        if ($request->label && $box->type == 1 && $box->has_label == 1) {
            $label = $request->label;
        }
        $cacheKey = 'timezone';
        $timezone = \Cache::rememberForever($cacheKey, function () {
            $setting = \App\Models\Setting::where('key', 'timezone')->first();
            return $setting?->value ?? 'UTC';
        });
        try {
            DB::beginTransaction();
            $box_use_data = [
                'box_id' => $box->id,
                'user_id' => $user->id,
                'coins' => $boxCoin,
                'start_at' => now()->setTimezone($timezone)->timestamp,
                'end_at' => now()->setTimezone($timezone)->addMinutes($box->duration)->timestamp,
                'room_uid' => $room->uid,
                'room_id' => $room->id,
                'users_num' => $request->users_num ?: $box->users,
                'used_num' => 0,
                'used_coins' => 0,
                'not_used_num' => $request->users_num ?: $box->users,
                'unused_coins' => $boxCoin,
                'type' => $box->type,
                'label' => $label,
                'image' => $box->image,
                'is_closed' => false,
            ];
            $boxU = BoxUse::query()->create(
                $box_use_data
            );

            $key  = 'BoxUse_' . $boxU->id;
            RedisService::updateUnSerialize($key, $box_use_data);

            $user->decrement('di', $box->coins);
            GiftLog::query()->create(
                [
                    'type' => 2,
                    'giftId' => $boxU->id,
                    'roomowner_id' => $room->uid,
                    'giftName' => 'luck box',
                    'giftNum' => 1,
                    'giftPrice' => $request->coins ?: $box->coins,
                    'sender_id' => $user->id,
                    'receiver_id' => 0,
                    'sender_family_id' => $user->family_id,
                ]
            );
            DB::commit();
            $c = BoxUse::query()->where('room_uid', $room->uid)->where('not_used_num', '>', 0)->count();
            $rem_time = Carbon::createFromTimestamp($boxU->start_at)->diffInSeconds($boxU->end_at);
            $m = [
                "messageContent" => [
                    "message" => "showluckybox",
                    "ownerBoxId" => $user->id,
                    "ownerBoxName" => $user->name,
                    "boxCoins" => $request->coins ?: $box->coins,
                    "boxId" => $boxU->id,
                    "boxType" => $box->type == 1 ? 'super' : 'normal',
                    "numOfBoxes" => (int)$c,
                    "ownerBoxImage" => $user->avatar,
                    "ownerBoxUId"  => $user->uuid,
                    'usersNum' => $request->users_num ?: $box->users,
                    'rem_time' => $rem_time,
                    'is_closed' => $box->is_closed,
                ]
            ];
            $json = json_encode($m);
            try {
                Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
                
                if ($box->type == 1) {
                    if (!$user instanceof User) return;
                    $d2 = [
                        "messageContent" => [
                            "message" => "bannerSuperBox",
                            'ownerRoomId' => $room->uid,
                            'isRoomPassword' => $room->room_pass ? true : false,
                            'ownerBoxid' => $user->id,
                            "ownerBoxName" => $user->name,
                            'coins' => $request->coins ?: $box->coins,
                            "ownerBoxImage" => $user->profile?->avatar ?? '',
                            "ownerBoxUId"  => $user->uuid,
                            "ownerBoxSL"  => $user->total_sender_level,
                            "ownerBoxRL"  => $user->total_received_level,
                            "ownerBoxAL"  => $user->UserVip?->level ?? 0,
                        ]
                    ];
                    $json2 = json_encode($d2);
                    dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json2, $user->id, $room->id, isExceptRoom: false), 'heavyProcessing');
                }
                dispatch(new SuperLuckyBoxJob())->delay(now()->setTimezone($cacheKey)->addMinutes(2))->onQueue('super-lucky');
                dispatch(new NormalLuckyBoxJop())->delay(now()->setTimezone($cacheKey)->addMinutes(2))->onQueue('normal-lucky');
            } catch (\Exception $exception) {
            }
            return Common::apiResponse(1, '', new BoxUseResource($boxU), 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $exception;
            return Common::apiResponse(0, 'fail', null, 400);
        }
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

        if (! $box_use['end_at'] < $timestamp) {
            return Common::apiResponse(0, __("box closed"), null, 404);
        }

        $box = Box::first($box_use['box_id']);

        if ($box->type == 0) // normal
        {
            $this->normalBox($box_use, $keyBoxUse, $user, $request);
        } else {  // super
            $this->superBox($box_use, $user, $keyBoxUse);
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
