<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Follow;
use App\Helpers\Common;
use App\Models\PickBoxList;
use App\Models\RoomVisitor;
use App\Models\UserBoxGift;
use App\Facades\RedisService;
use Illuminate\Bus\Queueable;
use App\Facades\CustomNotification;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\LuckyBoxServices;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class SuperLuckyBoxJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('im in the job');
        $timezone = Common::timeZone();
        $timestamp = Carbon::now($timezone)->timestamp;

        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 1)->where('is_closed', false)->get();

        if (!$userBoxes)  return;
        Log::info("boxUser");
        foreach ($userBoxes as $userBox) {
            Log::info("Processing Box ID: " . $userBox->id);
            $keyBoxUse  = 'BoxUse_' . $userBox->id;
            $pickerBoxIds =   PickBoxList::where('box_user_id', $userBox->id)->pluck('user_id')->toArray();
            if ($pickerBoxIds) {
                info('there are picked boxes');
                $users =  User::whereIn('id', $pickerBoxIds)->inRandomOrder()->get();
                foreach ($users as $user) {
                    Log::info("user " . $user->user_id);
                    $userInRoom =     RoomVisitor::where('user_id', $user->id)->exists();
                    if ($userInRoom) {
                        info('there are users in the room');
                        if ($userBox->users_num != $userBox->used_num) {
                            if (($userBox->not_used_num == 1)) {
                                $userBox->not_used_num = 0;
                                $fin = 1;
                            } else {
                                $fin = 0;
                                $userBox->not_used_num -= 1;
                            }

                            $giftService = new LuckyBoxServices();
                            $coins = $giftService->getCoins($fin, $userBox->unused_coins, $userBox->not_used_num);

                            //            put user in redis
                            $data = [
                                'box_uses_id' => $userBox->id,
                                'user_id' => $user->id,
                                'coins' => $coins,
                                'room_uid' => $userBox->room_uid,
                                'room_id' => $userBox->room_id,
                                'type' => $userBox->type,
                                'box_uses_owner_id' => $userBox->user_id,
                                'image' => $userBox->image,
                                'label' => $userBox->label
                            ];

                            if (!UserBoxGift::where(['user_id' => $user->id, 'box_uses_id' => $userBox->id])->exists()) {
                                Log::info("test111111111111 ");
                                UserBoxGift::query()->create($data);

                                $userBox['used_coins'] += $coins;
                                $userBox['used_num'] += 1;
                                $userBox['unused_coins'] -= $coins;
                                //update box use in redis
                                RedisService::updateUnSerialize($keyBoxUse, $userBox);
                                dispatch(new OpenBoxJob($userBox->id, $user->id, $user->name))->onQueue('luckyBox');

                                $user->increment('di', $coins);
                                if ($coins > 0) {
                                    CustomNotification::luckyBox($user, $coins, $userBox?->image);
                                }
                            }
                        }
                    }
                }
            }
            $user = User::where('id', $userBox->user_id)->first();
            $user->increment('di', $userBox->unused_coins);
            $userBox->is_closed = true;
            $userBox->save();
            $winners = UserBoxGift::where(['box_uses_id' => $userBox->id])->where('coins', '>', 0)->select('user_id', 'coins')->get()->toArray();
            $box_use = BoxUse::find($userBox->id);
            $room    = Room::withoutAppends()->where('uid', $box_use->room_uid)->first();

            $c     = BoxUse::query()->where('room_uid', $userBox->room_uid)->where('not_used_num', '>', 0)->count();
            $owner = User::withoutAppends()->select('id', 'name')->find($userBox->user_id);

            $pickerBoxIds =   PickBoxList::where('box_user_id', $userBox->id)->pluck('user_id')->toArray();
            info('boxes ids: '.$pickerBoxIds);
            $usersRoomVisit = RoomVisitor::where('room_id', $room->id)->whereNotIn('user_id', $pickerBoxIds)->whereHas('user')->pluck('user_id')->toArray();
            info('picked users inside the room : '.$usersRoomVisit);

            foreach ($usersRoomVisit as $userRoomVisit) {

                $m     = [
                    "messageContent" => [
                        "message"      => "hideluckybox",
                        "ownerBoxId"   => @$owner->id,
                        "ownerBoxName" => @$owner->name,
                        "boxCoins"     => $userBox->coins,
                        "boxId"        => $userBox->id,
                        "boxType"      => $userBox->type == 1 ? 'super' : 'normal',
                        "numOfBoxes"   => $c
                    ]
                ];
                $json  = json_encode($m);
                Common::sendToZego('SendCustomCommand', @$room->id, @$userRoomVisit->user_id, $json);
            }
            if ($room && $room->owner) {
                $m     = [
                    "messageContent" => [
                        "message"      => "winnerLuckyBox",

                        "boxUId" => $box_use->id,
                        "ownerId" =>  @$room->owner->id,
                        "ownerName" => @$room->owner->name ?? '',
                        "ownerImage" => @$room->owner->profile->avatar ?? '',
                        "ownerUuId" => @$room->owner->uuid,
                        "winners"     => $winners,
                    ]
                ];
                $json  = json_encode($m);
                Common::sendToZego('SendCustomCommand', @$room->id, @$room->owner->id, $json);
            }
        }
    }
}
