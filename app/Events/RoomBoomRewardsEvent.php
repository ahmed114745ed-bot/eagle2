<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomBoomRewardsEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rewardData;
    public $roomId;
    public function __construct($rewardData, $roomId)
    {
        $this->rewardData = $rewardData;
        $this->roomId = $roomId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('room.boom.rewards.' . $this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'room_boom_rewards';
    }
}
