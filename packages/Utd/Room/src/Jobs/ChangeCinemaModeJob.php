<?php

namespace Utd\Room\Jobs;

use Utd\Room\Entities\Room;
use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Utd\Room\Services\RoomUserService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class ChangeCinemaModeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $rooms = Room::where('mode', 5)->get();
        foreach ($rooms as $room) {
            $room->mode = 1;
            $room->save();
            $mode = 'party';
            $jsons = [];
            $map = [];
            $ms   = [
                'messageContent' => array_merge($map, ['message' => 'roomMode', 'mode' => $mode])
            ];
            $json = json_encode($ms);
            $jsons[] = $json;



            $jsons[] = $this->changeBackground($room, $room->uid, (new RoomUserService())->getRoomBackground($room));
            //  \Log::info("cinema mode");
            Common::sendToZego3('SendCustomCommand', $room->id, $room->uid, $jsons);
        }
    }

    public function changeBackground(Room $room, int $owner_id, string $image = ''): string|false
    {
        $data = [
            "messageContent" => [
                "message"       => "changeBackground",
                "imgbackground" => $image ?: "",
                "roomIntro"     => $room->room_intro ?: "",
                "roomImg"       => $room->room_cover ?: "",
                "room_type"     => @$room->myType->name ?: "",
                "room_name"     => @$room->room_name ?: ""
            ]
        ];
        $json = json_encode($data);
        //        Common::sendToZego('SendCustomCommand', $room->id, $owner_id, $json);
        return $json;
    }
}
