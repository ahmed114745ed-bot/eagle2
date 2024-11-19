<?php

namespace Modules\Chat\Http\Repositories;

use App\Models\User;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;

class ChatRepository
{
    public function findChatRoomBetweenUsers($userId, $userId2)
    {
        return ChatRoom::BetweenUsers($userId, $userId2)->first();
    }

    public function getUserByUUID($uuid){

        $chats = ChatRoom::where(function($q) use($uuid){
            $q->when($uuid, function($qf) use($uuid){
                $qf->whereHas('userOne', function($qq) use($uuid){
                    $qq->where('uuid', $uuid);
                })
                ->orWhereHas('userTwo', function($qq2) use($uuid){
                    $qq2->where('uuid', $uuid);
                });
            });
        })->get();

        return $chats;
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
        return ChatRoom::where(function ($query) use ($userId, $chatRoomId) {
                $query->where('user_id', $userId)->orWhere('user_id2', $userId);
            })
            ->where('id', $chatRoomId)
            ->first();
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
