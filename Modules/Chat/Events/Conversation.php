<?php

namespace Modules\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Conversation implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message ;
    public $user2 ;
    public $check_room ;
    public function __construct($message ,$user2 ,$check_room)
    {
        $this->message = $message;
        $this->user2 = $user2;
        $this->check_room = $check_room;
    }

    public function broadcastOn()
    {
        return ['conversation-'.$this->check_room->id];
    }

    public function broadcastAs()
    {
        return 'update-conversation-list';
    }

    public function broadcastWith() : array
    {
        $data = (array) $this->message;

        Log::info('Broadcasting Conversation Event', [
            'chat_room_id' => $this->check_room->id,
            'message_id' => $this->message->id,
            'status' => $this->message->status,
            'payload' => $data
        ]);
    
        return $data;
    
    }
}
