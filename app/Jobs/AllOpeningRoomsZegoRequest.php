<?php

namespace App\Jobs;

use App\Events\BannerEvent;
use App\Events\RoomEvent;
use App\Events\SuperLuckyBox;
use App\Helpers\Common;
use App\Models\Config;
use App\Models\Room;
use GuzzleHttp\Promise\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        // Log::info("event is running banner event");
        event(new BannerEvent(json_decode($this->json, true)));
    }


}
