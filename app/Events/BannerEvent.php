<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use Illuminate\Queue\SerializesModels;

class BannerEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;
    public string $channel;
    public function __construct($data)
    {
        $this->data = $data;
        $this->channel = $data['messageContent']['event'];

        \Log::info('🚀 BannerEvent constructed', [
            'channel' => $this->channel,
            'data' => $this->data,
        ]);
    }

    public function broadcastOn()
    {
        return new Channel(str_replace('.', '-', $this->channel));
    }
    
    public function broadcastAs()
    {
        return str_replace('.', '-', $this->channel);
    }

    public function broadcastWith()
    {
        \Log::info('📦 BannerEvent broadcastWith called', [
            'payload' => $this->data,
        ]);
        return $this->data;
    }
}
