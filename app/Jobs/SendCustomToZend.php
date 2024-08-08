<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\Room;
use http\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomToZend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $message;
    private $roomIds;
    private $firstRoom;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId, $firstRoom, $message, $roomIds)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->roomIds = $roomIds;
        $this->firstRoom = $firstRoom;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->sendDataToZego($this->userId, $this->message, $this->roomIds, $this->firstRoom);

    }

    private function sendDataToZego($userId, String $message, array $roomsIds,$firstRoomId)
    {
        // get room
        $room = Room::query()->find($firstRoomId);
        $ms = [
            'messageContent'=>[
                'msg'=>'yellowBanner',
                'uId'=>$userId,
                'umsg'=>$message,
                'oid' => @$room->uid,
                'ps' => @$room->room_pass != null || @$room->room_pass != '', // password_status
            ]
        ];
        $json = json_encode($ms);

        if ($firstRoomId != null){
            Common::sendToZego ('SendCustomCommand',$firstRoomId,$userId,$json);

        }
        foreach ($roomsIds as $roomId) {
            if($firstRoomId == $roomId) continue;
            Common::sendToZego ('SendCustomCommand',$roomId,$userId,$json);
        }
    }
}
