<?php

namespace App\Jobs;

use App\Classes\Gifts\SendGiftService;
use App\Models\Pk;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class UpdatePkAndSendToZigo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $userId;
    private $receivedIds;
    private $totalPrice;
    private $roomId;
    private string $roomMics;


    /**
     * @param array $receivedIds
     * @param float $totalPrice
     * @param string $roomMics
     * @param $userId
     */
    public function __construct($userId, $roomId, array $receivedIds, float $totalPrice, string $roomMics)
    {

        $this->receivedIds = $receivedIds;
        $this->totalPrice  = $totalPrice;
        $this->roomMics    = $roomMics;
        $this->userId      = $userId;
        $this->roomId      = $roomId;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
          
        Log::info(12345);
        /*$lastPk =
            Pk::query()->where('room_id', $this->roomId)->where('status', 1)->whereDate('end_at', "<=", now())->orderByDesc('id')->first();*/

        $lastPk = Room::select(['id'])->where('id', $this->roomId)->first()?->lastPk;
        Log::info(' this is job pk '. json_encode($lastPk));
        (new SendGiftService())->updatePkScoresAndSendToZegoJob($lastPk, $this->userId, $this->roomId, $this->receivedIds, $this->totalPrice, $this->roomMics);
    }


}
