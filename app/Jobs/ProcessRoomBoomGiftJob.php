<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\RoomBoom\Services\NewRoomBoomGiftService;

class ProcessRoomBoomGiftJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $room;
    public $totalPrice;
    public $userId;
    public $roomBoomSettings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($room, $totalPrice, $userId, $roomBoomSettings = 1)
    {
        // Don't serialize the full room model if it's heavy, just what we need, or let SerializesModels handle it
        $this->room = $room;
        $this->totalPrice = $totalPrice;
        $this->userId = $userId;
        $this->roomBoomSettings = $roomBoomSettings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->roomBoomSettings) {
            (new NewRoomBoomGiftService())->sendGift($this->room, $this->totalPrice, $this->userId);
        } else {
            $tz = getTimezone();
            $todayStart = \Carbon\Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');

            $totalRoomGift = (new NewRoomBoomGiftService())->getOrCreateTotalRoomGift($this->room->id, $todayStart);

            $totalRoomGift->increment('current_total', $this->totalPrice);
        }
    }
}
