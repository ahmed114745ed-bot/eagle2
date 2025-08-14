<?php

namespace Modules\RoomBoom\Jobs;

use App\Events\EndRoomBoomEvent;
use App\Helpers\Common;
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
    public $newTotal;
    public $userId;
    public $room;

    public function __construct($currentLevel, $newTotal, $userId, $room)
    {
        $this->currentLevel = $currentLevel;
        $this->newTotal = $newTotal;
        $this->userId = $userId;
        $this->room = $room;
    }

    public function handle(): void
    {
        $user = User::with('profile')->where('id', $this->userId)->first();

        $gift_data = [
            'room_id'           => $this->room->id,
            'room_uuid'         => $this->room->owner?->uuid ?: 0,
            'room_owner_id'     => $this->room->uid ?: 0,
            'is_password'       => (bool)(@$this->room->room_pass),
            'gift_price'        => $this->newTotal,
            'room_name'         => $this->room->room_name ?: '',
            "room_cover"        => $this->room->room_cover ?? '',
            "room_background"   => $this->room->final_room_image ?? '',
            "room_mode"         => $this->room->mode,
            'roomBoomLevel'     => $this->currentLevel,
            'duration'          => 30,
            'user_image'        => $user->profile->avatar ?? '',
        ];

        event(new EndRoomBoomEvent($gift_data));

        sleep(20);

        $this->send(10);
    }

    public function send($duration): void
    {
        $d = [
            "messageContent" => [
                "message" => "roomBoomEnded",
                'roomBoomLevel' => $this->currentLevel,
                'duration' => $duration,
            ]
        ];
        $json = json_encode($d);

        Common::sendToZego('SendCustomCommand', $this->room->id, $this->room->uid, $json);
    }
}
