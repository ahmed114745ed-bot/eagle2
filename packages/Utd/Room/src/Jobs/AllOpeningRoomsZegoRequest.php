<?php

namespace Utd\Room\Jobs;

use App\Events\BannerEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AllOpeningRoomsZegoRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $json;
    private $senderId;
    private $roomID;
    private bool $isExceptRoom;

    public function __construct($json, $senderId, $roomID, $isExceptRoom = true)
    {
        $this->json = $json;
        $this->senderId = $senderId;
        $this->roomID = $roomID;
        $this->isExceptRoom = $isExceptRoom;
    }

    public function handle()
    {
        event(new BannerEvent(json_decode($this->json, true)));
    }
}
