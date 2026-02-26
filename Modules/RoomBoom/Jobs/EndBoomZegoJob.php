<?php

namespace Modules\RoomBoom\Jobs;

use App\Events\EndRoomBoomEvent;
use App\Helpers\Common;
use App\Models\Room;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EndBoomZegoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $currentLevel;
    public $room;

    public function __construct($currentLevel, $room)
    {
        $this->currentLevel = $currentLevel;
        $this->room = gettype($room) == 'integer' ? Room::find($room) : $room;
    }

    public function handle(): void
    {
        info('in end room zego job');

        $d = [
            "messageContent" => [
                "message" => "roomBoomEnded",
                'roomBoomLevel' => $this->currentLevel->level,
                'duration' => 10,
                'video' => $this->currentLevel->video,
                'video_type' => $this->currentLevel->image_type ?? 'mp4',
            ]
        ];
        $json = json_encode($d);

        Common::sendToZego('SendCustomCommand', $this->room->id, $this->room->uid, $json);
    }
}
