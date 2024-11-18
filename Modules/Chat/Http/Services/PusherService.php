<?php

namespace Modules\Chat\Http\Services;

use App\Models\User;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Http\Repositories\ChatRepository;
use Modules\Chat\Jobs\ReciveChatMessagejob;

class PusherService
{
    protected $chatRoomRepo;

    public function __construct( ChatRepository $chatRoomRepo)
    {
        $this->chatRoomRepo = $chatRoomRepo;
    }

    public function handleUserStatusChange(string $channel, string $eventName): void
    {
        $parts = explode('-', $channel);

        if (count($parts) === 2) {
            $channelName = $parts[0];
            $userId = $parts[1];

            if ($channelName == 'user') {
                $user = User::find($userId);

                if ($user) {
                    if ($eventName == 'channel_vacated') {
                        // Mark user as offline
                        $this->setUserOffline($user);
                    } else {
                        // Mark user as online and handle unread messages
                        $this->setUserOnlineAndHandleUnreadMessages($user);
                    }
                }
            }
        }
    }

    private function setUserOffline($user)
    {
        $user->online = 0;
        $user->current_room_chat = null;
        $user->save();
    }

    private function setUserOnlineAndHandleUnreadMessages($user)
    {
        $user->online = 1;
        $user->save();

        // Get chat rooms for the user
        $chatsId = $this->chatRoomRepo->getUserChatRooms($user->id);

        // Get unread messages and dispatch job
        $totalUnread = ChatMessage::whereIn('chat_room_id', $chatsId)
            ->where('user_id', '!=', $user->id)
            ->where('status', 'sended')
            ->get();

        dispatch(new ReciveChatMessagejob($totalUnread, 'received'));
    }
}
