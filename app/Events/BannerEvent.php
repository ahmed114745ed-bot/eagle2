<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BannerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('banner');
    }

    public function broadcastAs()
    {
        return 'banner';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
