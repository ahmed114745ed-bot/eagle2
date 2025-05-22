<?php

namespace App\Jobs;

use App\Helpers\Common;
use App\Models\Room;
use GuzzleHttp\Promise\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;


class AllOpeningRoomsZegoRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $json;
    private $senderId;
    private $roomID;
    private bool $isExceptRoom;


    /**
     * Create a new job instance.
     *
     * @param $json
     * @param $senderId
     */
    public function __construct($json, $senderId, $roomID, $isExceptRoom = true)
    {
        //
        $this->json = $json;
        $this->senderId = $senderId;
        $this->roomID = $roomID;
        $this->isExceptRoom = $isExceptRoom;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*$rooms = Cache::remember('allRooms', 60, function (){
            return Room::withoutAppends()->where('room_status', 1)->where(function ($q) {
                $q->where('is_afk', 1)->orWhere('count_room_socket', '!=', 0);
            })->select(['id'])->get();
        });

        foreach ($rooms as $r) {
            if ($r->id == $this->roomID) continue;
            Common::sendToZego('SendCustomCommand', $r->id, $this->senderId, $this->json);
        }*/

        $rooms = Cache::remember('allRooms', 60, function (){
            return Room::withoutAppends()->where('room_status', 1)->where(function ($q) {
                $q->has('roomVisitors');
            })->select(['id'])->get();
        });

        $rooms = $rooms->pluck('id');

        $chunk = $rooms->chunk(15);
        foreach ($chunk as $roomIds) {
            $promises = Common::sendToZegoWithArrayOfRooms('SendCustomCommand', $roomIds->toArray(), $this->senderId, $this->json, exceptRoomId: $this->isExceptRoom ? $this->roomID : null);
            Utils::unwrap($promises);
        }
    }


}
