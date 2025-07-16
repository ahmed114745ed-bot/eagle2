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
use Illuminate\Queue\SerializesModels;
use App\Http\Services\LuckyBoxServices;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class NormalLuckyBoxJop implements ShouldQueue
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
        $timezone = Common::timeZone();
        $timestamp = Carbon::now($timezone)->timestamp;

        $userBoxes = BoxUse::where('end_at', '<', $timestamp)->where('type', 0)->where('is_closed', false)->get();
        if (!$userBoxes) return;

        foreach ($userBoxes as $userBox) {
            $user = User::where('id', $userBox->user_id)->first();
            $user->increment('di', $userBox->unused_coins);
            $userBox->is_closed = true;
            $userBox->save();

            $room = Room::withoutAppends()->where('uid', $userBox->room_uid)->select('id')->first();
            $c = BoxUse::query()->where('room_uid', $userBox->room_uid)->where('not_used_num', '>', 0)->count();
            $owner = User::withoutAppends()->select('id', 'name')->find($userBox->user_id);

            $usersRoomVisit = RoomVisitor::where('room_id', $room->id)->pluck('user_id')->toArray();


            foreach ($usersRoomVisit as $userRoomVisit) {

                $m = [
                    "messageContent" => [
                        "message" => "hideluckybox",
                        "ownerBoxId" => @$owner->id,
                        "ownerBoxName" => @$owner->name,
                        "boxCoins" => $userBox->coins,
                        "boxId" => $userBox->id,
                        "boxType" => $userBox->type == 1 ? 'super' : 'normal',
                        "numOfBoxes" => $c
                    ]
                ];
                $json = json_encode($m);
                Common::sendToZego('SendCustomCommand', @$room->id, @$userRoomVisit->user_id, $json);
            }
        }
    }
}
