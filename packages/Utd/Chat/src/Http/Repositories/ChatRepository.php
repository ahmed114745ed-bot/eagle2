<?php

namespace Utd\Chat\Http\Repositories;

use App\Models\User;
use Utd\Chat\Entities\ChatMessage;
use Utd\Chat\Entities\ChatRoom;

class ChatRepository
{
    public function findChatRoomBetweenUsers($userId, $userId2)
    {
        $chatRoom = ChatRoom::BetweenUsers($userId, $userId2)->first();

        if ($chatRoom) {
            if ($chatRoom->user_1_deleted) {
                $chatRoom->update(['user_1_deleted' => null]);
            }

            if ($chatRoom->user_2_deleted) {
                $chatRoom->update(['user_2_deleted' => null]);
            }
        }

        return $chatRoom;
    }

    public function getUserChatRooms(int $userId): array
    {
        return ChatRoom::where('user_id', $userId)
            ->orWhere('user_id2', $userId)
            ->pluck('id')
            ->toArray();
    }

    public function findChatRoomForUser(int $userId, string $chatRoomId): ?ChatRoom
    {
        return ChatRoom::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)->orWhere('user_id2', $userId);
        })
            ->where('id', $chatRoomId)
            ->first();
    }

    public function countMessagesByUserInRoom($chatRoomId, $userId)
    {
        return ChatMessage::ByUserInRoom($chatRoomId, $userId)->count();
    }

    public function countDistinctUsersInRoom($chatRoomId)
    {
        return ChatMessage::distinctUserInRoom($chatRoomId)->count();
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
        if ($message->user_id === $userId) {
            $message->user_1_deleted = now();
        } else {
            $message->user_2_deleted = now();
        }

        return $message->save();
    }

    public function getUserByUUID($uuid)
    {
        return User::where('uuid', $uuid)->first();
    }
}
