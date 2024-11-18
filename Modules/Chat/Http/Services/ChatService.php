<?php

namespace Modules\Chat\Http\Services;

use Exception;
use Illuminate\Support\Facades\Event;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Events\CardDeleteMessage;
use Modules\Chat\Events\DeleteMessage;
use Modules\Chat\Http\Repositories\ChatRepository;
use Modules\Chat\Http\Repositories\BlacklistRepository;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;

class ChatService
{
    protected $chatRepository;
    protected $blacklistRepository;

    public function __construct(
        ChatRepository $chatRepository,
        BlacklistRepository $blacklistRepository,
    ) {
        $this->chatRepository = $chatRepository;
        $this->blacklistRepository = $blacklistRepository;
    }

    public function isUserBlocked($userId, $fromUserId)
    {
        return $this->blacklistRepository->isUserBlocked($userId, $fromUserId);
    }

    public function findChatRoomBetweenUsers($userId, $userId2)
    {
        return $this->chatRepository->findChatRoomBetweenUsers($userId, $userId2);
    }

    public function countMessagesByUserInRoom($chatRoomId, $userId)
    {
        return $this->chatRepository->countMessagesByUserInRoom($chatRoomId, $userId);
    }

    public function createChatMessage(array $data)
    {
        return $this->chatRepository->createChatMessage($data);
    }

    public function updateMessage($messageId, $newMessage, $user)
    {
        $message = $this->chatRepository->findChatMessageById($messageId);

        if (!$message || $message->user_id !== $user->id) {
            return ['status' => 404, 'message' => 'Unauthorized'];
        }

        if (!$message->canBeEdited()) {
            return [
                'status' => 404,
                'message' => 'Deletion is permissible within 15 minutes after sending.',
            ];
        }

        $updatedMessage = $this->chatRepository->updateMessage($message, $newMessage);
        return $updatedMessage;
    }


    public function deleteMessages(array $ids, $user)
    {
        $chatRoomId = null;

        foreach ($ids as $id) {
            $message = $this->chatRepository->findChatMessageById((int)$id);

            if (!$message || $message->user_id !== $user->id) {
                return [
                    'status' => 404,
                    'message' => 'Unauthorized or message not found',
                ];
            }

            if (!$message->created_at->greaterThanOrEqualTo(now()->subDay())) {
                return [
                    'status' => 404,
                    'message' => 'Deletion is permissible within a day after sending.',
                ];
            }

            $chatRoomId = $message->chat_room_id;
        }

        $this->chatRepository->deleteMessages($ids);

        $chatRoom = ChatRoom::find($chatRoomId);
        $otherUser = $chatRoom->user_id === $user->id
            ? $chatRoom->user_id2
            : $chatRoom->user_id;

        $roomResource = new ChatRoomResourcePusher($chatRoom);

        Event::dispatch(new DeleteMessage($ids, $otherUser, $roomResource));
        Event::dispatch(new CardDeleteMessage($roomResource->toResponse(request())->getData()->data, $otherUser));

        return [
            'status' => 200,
            'message' => 'Messages deleted successfully',
        ];
    }

    public function deleteForUser(array $ids, $user)
    {
        foreach ($ids as $id) {
            $message = $this->chatRepository->findChatMessageById($id);

            if (!$message) {
                return [
                    'status' => 404,
                    'message' => 'Message not found',
                ];
            }

            $chatRoom = ChatRoom::find($message->chat_room_id);

            if (!$chatRoom || ($chatRoom->user_id !== $user->id && $chatRoom->user_id2 !== $user->id)) {
                return [
                    'status' => 404,
                    'message' => 'Unauthorized access to the message',
                ];
            }

            $this->chatRepository->updateDeletedTimestamp($message, $user->id);
        }

        return [
            'status' => 200,
            'message' => 'Messages marked as deleted',
        ];
    }
}
