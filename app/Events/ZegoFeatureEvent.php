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

class ZegoFeatureEvent implements ShouldBroadcastNow //ShouldBroadcast
{
//    use Dispatchable, InteractsWithSockets, SerializesModels;
    use InteractsWithSockets;

    public $zegoFeature;

    /**
     * Create a new event instance.
     *
     * @param array $gift
     */
    public function __construct($zegoFeature)
    {
        $this->zegoFeature = $zegoFeature;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('zego_feature');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'zego_feature';
    }
}
