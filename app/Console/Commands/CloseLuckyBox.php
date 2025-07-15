<?php

namespace App\Console\Commands;


use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\BoxUse;
use App\Helpers\Common;
use App\Models\PickBoxList;
use App\Models\RoomVisitor;
use Illuminate\Console\Command;


class CloseLuckyBox extends Command
{
    protected $signature = 'closeBox';



    public function handle()
    {
        $timezone = Common::timeZone();
        $timestamp = Carbon::now($timezone)->timestamp;

        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 1)->where('is_closed', false)->get();
        if (!$userBoxes)  return;
        foreach ($userBoxes as $userBox) {

            $room    = Room::withoutAppends()->where('uid', $userBox->room_uid)->select('id')->first();
            $c     = BoxUse::query()->where('room_uid', $userBox->room_uid)->where('not_used_num', '>', 0)->count();
            $owner = User::withoutAppends()->select('id', 'name')->find($userBox->user_id);
            if ($userBox->type == 0) {
                $usersRoomVisit = RoomVisitor::where('room_id', $room->id)->pluck('user_id')->toArray();
            } else {
                $pickerBoxIds =   PickBoxList::where('box_user_id', $userBox->id)->pluck('user_id')->toArray();
                $usersRoomVisit = RoomVisitor::where('room_id', $room->id)->whereNotIn('user_id', $pickerBoxIds)->whereHas('user')->pluck('user_id')->toArray();
            }

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
            $this->info(now()->toDateTimeString() . ' ' . ' box close successful...');
        }
    }
}
