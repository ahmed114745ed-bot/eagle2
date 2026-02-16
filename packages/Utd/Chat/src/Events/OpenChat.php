<?php

namespace Utd\Chat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpenChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public $user2;

    public $check_room;

    public $isConversation;

    public function __construct($chat, $user2, $check_room, $isConversation = true)
    {
        $this->chat = $chat;
        $this->user2 = $user2;
        $this->check_room = $check_room;
        $this->isConversation = $isConversation;
    }

    public function broadcastOn(): array
    {
        if ($this->isConversation) {
            return [
                'user-'.$this->user2->id,
                'conversation-'.$this->check_room->id,
            ];
        }

        return [
            'user-'.$this->user2->id,
        ];

        //        return ['user-'.$this->user2->id ,'conversation-'.$this->check_room->id];
    }

    public function broadcastAs()
    {
        return 'open_chat';
    }

    public function broadcastWith(): array
    {
        return (array) $this->chat;
    }
}
