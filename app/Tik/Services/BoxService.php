<?php

namespace App\Tik\Services;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\UserVip;
use App\Models\CoreWallet;
use App\Facades\RedisService;
use App\Jobs\SuperLuckyBoxJob;
use App\Jobs\NormalLuckyBoxJop;
use Illuminate\Support\Facades\DB;
use App\Jobs\AllOpeningRoomsZegoRequest;
use Modules\Events\Entities\WinnerReward;
use App\Http\Resources\UserReportResource;
use App\Http\Resources\ReportEventResource;
use Modules\Events\Entities\RewardWinnerPk;
use App\Http\Resources\AgencyReportResource;
use App\Tik\Repositories\BlackLisRepository;
use App\Http\Resources\Api\V1\BoxUseResource;
use Modules\Events\Services\LoseWinnerRewards;
use App\Http\Resources\AdminUserReportResource;
use Modules\Achievement\Entities\UserAchievementLevel;

class BoxService
{
    public function __construct() {}

    public function sendBox($request, $user, $box, $room, $timezone, $label)
    {
        $boxCoin = $this->calculationSendBox($box);
        DB::beginTransaction();
        if ($box->type == 0) {
            $boxU = $this->sendNormalBox($box, $request, $boxCoin, $label, $room, $user->id, $timezone);
        } else {
            $boxU = $this->sendSuperBox($box, $request,  $boxCoin, $label, $room, $user, $timezone);
        }

        $user->decrement('di', $box->coins);
        try {
            DB::commit();
            $c = BoxUse::query()->where('room_uid', $room->uid)->where('not_used_num', '>', 0)->count();
            $rem_time = Carbon::createFromTimestamp($boxU->start_at)->diffInSeconds(
                Carbon::createFromTimestamp($boxU->end_at)
            );
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

            Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
            return Common::apiResponse(1, '', new BoxUseResource($boxU), 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $exception;
            return Common::apiResponse(0, 'fail', null, 400);
        }
    }

    public function sendNormalBox($box, $request, $boxCoin, $label, $room, $userId, $timezone)
    {
        $normalDuration = Common::getConf('normal_box_duration') ?? 1;
        $box_use_data = [
            'box_id' => $box->id,
            'user_id' => $userId,
            'coins' => $boxCoin,
            'start_at' => now()->setTimezone($timezone ?? 'UTC')->timestamp,
            'end_at' => now()->setTimezone($timezone ?? 'UTC')->addSeconds($normalDuration)->timestamp,
            'room_uid' => $room->uid,
            'room_id' => $room->id,
            'users_num' =>  $request->users_num,
            'used_num' => 0,
            'used_coins' => 0,
            'not_used_num' =>  $request->users_num,
            'unused_coins' => $boxCoin,
            'type' => $box->type,
            'label' => $label,
            'image' => $box->image,
            'is_closed' => false,
        ];

        $boxUser = BoxUse::query()->create(
            $box_use_data
        );
        $key  = 'BoxUse_' . $boxUser->id;
        RedisService::updateUnSerialize($key, $box_use_data);
        dispatch(new NormalLuckyBoxJop())->delay(now()->setTimezone($timezone ?? 'UTC')->addSecond(30));
        return $boxUser;
    }

    public function sendSuperBox($box, $request,  $boxCoin, $label, $room, $user, $timezone)
    {
        $box_use_data = [
            'box_id' => $box->id,
            'user_id' => $user->id,
            'coins' => $boxCoin,
            'start_at' => now()->setTimezone($timezone ?? 'UTC')->timestamp,
            'end_at' => now()->setTimezone($timezone ?? 'UTC')->addMinutes($box->duration)->timestamp,
            'room_uid' => $room->uid,
            'room_id' => $room->id,
            'users_num' =>  $box->users,
            'used_num' => 0,
            'used_coins' => 0,
            'not_used_num' =>  $box->users,
            'unused_coins' => $boxCoin,
            'type' => $box->type,
            'label' => $label,
            'image' => $box->image,
            'is_closed' => false,
        ];
        dispatch(new SuperLuckyBoxJob())->delay(now()->setTimezone($timezone ?? 'UTC')->addMinutes(2))->onQueue('super-lucky');
        $boxUser = BoxUse::query()->create(
            $box_use_data
        );
        $key  = 'BoxUse_' . $boxUser->id;
        RedisService::updateUnSerialize($key, $box_use_data);
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
        return $boxUser;
    }

    public function calculationSendBox($box)
    {
        $app_percentage = Config::query()->where('name', 'app_wallet_lucky_box')->first()?->value ?? 2;
        $walletCoins = ($box->coins * $app_percentage) / 100;
        $boxCoin = $box->coins - $walletCoins;
        $walletApp = CoreWallet::where('name', 'lucky_box')->first();
        $newWalletCoins = $walletApp->coins + $walletCoins;
        $walletApp->update([
            'coins' => $newWalletCoins,
        ]);
        return $boxCoin;
    }
}
