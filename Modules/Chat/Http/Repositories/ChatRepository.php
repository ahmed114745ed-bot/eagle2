<?php

namespace Modules\Chat\Http\Repositories;

use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;

class ChatRepository
{
    public function findChatRoomBetweenUsers($userId, $userId2)
    {
        return ChatRoom::BetweenUsers($userId, $userId2)->first();
    }

    public function countMessagesByUserInRoom($chatRoomId, $userId)
    {
        return ChatMessage::ByUserInRoom($chatRoomId, $userId)->count();
    }

    public function createChatMessage($data)
    {
        return ChatMessage::create($data);
    }

    public function findChatMessageById($id)
    {
        return ChatMessage::find($id);
    }

    public function updateMessage(ChatMessage $message, $newContent)
    {
        $message->message = $newContent;
        $message->save();

        return $message;
    }

    public function deleteMessages(array $ids)
    {
        return ChatMessage::whereIn('id', $ids)->delete();
    }

    public function updateDeletedTimestamp(ChatMessage $message, $userId)
    {
        if ($message->user_id == $userId) {
            $message->user_1_deleted = now();
        } else {
            $message->user_2_deleted = now();
        }
        return $message->save();
    }

}
