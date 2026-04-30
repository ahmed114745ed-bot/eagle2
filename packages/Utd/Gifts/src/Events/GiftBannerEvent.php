<?php

namespace Utd\Gifts\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftBannerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gift;

    public string $broadcastQueue;

    public function __construct($gift)
    {
        $this->gift = $gift;
        $this->broadcastQueue = getLeastBusyQueue('heavyProcessing');
    }

    public function broadcastOn()
    {
        return new Channel('gift_banner');
    }

    public function broadcastAs()
    {
        return 'gift_banner';
    }
}
