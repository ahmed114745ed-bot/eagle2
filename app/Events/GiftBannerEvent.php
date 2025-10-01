<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftBannerEvent implements ShouldBroadcastNow //ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    // use InteractsWithSockets;

    public $gift;

    /**
     * Create a new event instance.
     *
     * @param array $gift
     */
    public function __construct($gift)
    {
        info('in gift logs');
        $this->gift = $gift;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('gift_banner');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'gift_banner';
    }
}
