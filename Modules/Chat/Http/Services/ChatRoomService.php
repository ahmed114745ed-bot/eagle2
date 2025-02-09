<?php

namespace Modules\Chat\Http\Services;

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Entities\MessageAlbum;
use Modules\Chat\Entities\React;
use Modules\Chat\Events\OpenChat;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\Chat\Jobs\SendMessageToAllUsers;
use Illuminate\Database\Eloquent\Builder;
use Modules\Reals\Entities\Real;

class ChatRoomService
{
    /**
     * Handle the invitation logic.
     *
     * @param array  $data
     * @param int    $userId
     * @param string $type
     * @param array  $userIds
     * @param array  $exceptIds
     * @return void
     */
    public function handleInvite(array $data, int $userId, string $type, array $userIds = [], array $exceptIds = [])
    {
        if ($type === 'all') {
            $this->inviteToAll($userId, $data, $exceptIds);
        } else {
            $this->inviteToSpecificUsers($userId, $data, $userIds);
        }

        $this->countReel($data, $type , $userId, $userIds);
    }

    public function countReel($data,  $type , $userId, $userIds)
    {
        $parts = explode(':', str_replace("\n", ':', $data['message']));
        $reelId = $parts[4] ?? null;
        Log::info('reel id : ' . $reelId);
        $reel = Real::find($reelId);
        if (!$reel) return true;
        $user = User::find($userId);
        if ($type == 'all' && $userIds == null) {
            $reel->share_num += $user->friend;
        } elseif ($type == 'not' && $userIds != null) {
            $reel->share_num += ($user->friend - count($userIds));
        } elseif ($type == 'one') {
            $countUsers = count($userIds);
            $reel->share_num += $countUsers;
        }
        $reel->save();
        return true;
    }

    /**
     * Invite all followers and followeds except specified IDs.
     *
     * @param int   $userId
     * @param array $data
     * @param array $exceptIds
     * @return void
     */
    private function inviteToAll(int $userId, array $data, array $exceptIds)
    {
        User::query()
            ->select('id')
            ->whereHas('followers', fn($q) => $q->where('user_id', $userId))
            ->whereHas('followeds', fn($q) => $q->where('followed_user_id', $userId))
            ->whereNotIn('id', $exceptIds)
            ->chunk(400, function ($users) use ($userId, $data) {
                $userIds = $users->pluck('id')->toArray();
                $this->sendMessageToUsers($userId, $userIds, $data);
            });
    }

    /**
     * Invite specific users.
     *
     * @param int   $userId
     * @param array $data
     * @param array $userIds
     * @return void
     */
    private function inviteToSpecificUsers(int $userId, array $data, array $userIds)
    {
        if (!empty($userIds)) {
            $this->sendMessageToUsers($userId, $userIds, $data);
        }
    }

    public function sendMessageToUsers(int|string|null $userId, mixed $userIds, array $message): void
    {
        $timeZone = request()->hasHeader('tz') ? request()->header()['tz'][0] : 'UTC';
        dispatchJobToQueue(new SendMessageToAllUsers($userId, $userIds, $message, timezone: $timeZone), 'heavyProcessing');
        // $userIds = $this->sendMessages($userId, $userIds, $message);

        // $this->createNewChatRooms($userIds, $userId);

        // $this->sendMessages($userId, $userIds, $message);
    }

    public function findUsersByName(string $name)
    {
        return User::where('name', 'LIKE', '%' . $name . '%')
            ->select('id', 'name')
            ->get();
    }

    public function getChatRooms($user, $uuid)
    {
        $user = User::with('chats')->find($user->id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'user not found',
                'status' => 200,
            ];
        }

        // Get top chats
        $topChats = $user->chats->pluck('id')->toArray();

        // Get user chats (friends)
        $friends = ChatRoom::WhereHas('messages')
            ->select(
                'chat_rooms.*',
                DB::raw('(SELECT MAX(created_at) FROM chat_messages WHERE chat_messages.chat_room_id = chat_rooms.id) AS last_message_created_at')
            )
            ->where(function ($q) use ($topChats, $user) {
                $q->where(function ($query) use ($topChats, $user) {
                    $query->whereNotIn('chat_rooms.id', $topChats)
                        ->where('chat_rooms.user_id', $user->id)
                        ->where('chat_rooms.type', 'friends');
                })
                    ->orWhere(function ($query) use ($topChats, $user) {
                        $query->whereNotIn('chat_rooms.id', $topChats)
                            ->where('chat_rooms.user_id2', $user->id)
                            ->where('chat_rooms.type', 'friends');
                    });
            })
            ->when($uuid, function ($q) use ($uuid) {
                $q->where(function ($q) use ($uuid) {
                    $q->whereHas('userOne', function ($qq) use ($uuid) {
                        $qq->where('uuid', 'like', "%$uuid%");
                    })
                        ->orWhereHas('userTwo', function ($qq2) use ($uuid) {
                            $qq2->where('uuid', 'like', "%$uuid%");
                        });
                });
            })
            ->groupBy([
                'chat_rooms.id',
                'chat_rooms.user_id',
                'chat_rooms.user_id2',
                'chat_rooms.type',
                'user_1_deleted',
                'user_2_deleted',
                'created_at',
                'updated_at',
            ])
            ->orderByDesc('last_message_created_at')
            ->paginate(20);

        // // Get chat requests (guest)
        // $guestChats = ChatRoom::WhereHas('messages')
        //     ->select('chat_rooms.*')
        //     ->where('chat_rooms.user_id2', $user->id)
        //     ->where('chat_rooms.type', 'guest')
        //     ->with('messages')
        //     ->join('chat_messages', 'chat_rooms.id', '=', 'chat_messages.chat_room_id')
        //     ->orderBy('chat_messages.id', 'desc')
        //     ->paginate(20);

        // Get unread messages
        $chatRoomIds = ChatRoom::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('user_id2', $user->id);
        })->where('type', 'friends')->pluck('id')->toArray();

        $unreadMessages = ChatMessage::whereIn('chat_room_id', $chatRoomIds)
            ->where('user_id', '!=', $user->id)
            ->where('status', '!=', 'seen')
            ->paginate(20);

        return [
            'success' => true,
            'message' => 'successfully',
            'data' => [
                'top_chats' => ChatRoomResource::collection($user->chats),
                'chat' => ChatRoomResource::collection($friends),
                // 'request_chat' => ChatRoomResource::collection($guestChats),
                'total_unread_messages' => $unreadMessages->count(),
                'unread_messages' => ChatMessageResource::collection($unreadMessages),
            ],
            'status' => 200,
        ];
    }

    public function getGUestChatRooms($user)
    {
        $user = User::with('chats')->find($user->id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'user not found',
                'status' => 200,
            ];
        }

        // Get chat requests (guest)
        $guestChats = ChatRoom::WhereHas('messages')
            ->select('chat_rooms.*')
            ->where('chat_rooms.user_id2', $user->id)
            ->where('chat_rooms.type', 'guest')
            ->has('messages')
            // ->join('chat_messages', 'chat_rooms.id', '=', 'chat_messages.chat_room_id')
            // ->orderBy('chat_messages.id', 'desc')
            ->paginate(20);

        // Get unread messages
        $chatRoomIds = ChatRoom::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->orWhere('user_id2', $user->id);
        })->where('type', 'guest')->pluck('id')->toArray();

        $unreadMessages = ChatMessage::whereIn('chat_room_id', $chatRoomIds)
            ->where('user_id', '!=', $user->id)
            ->where('status', '!=', 'seen')
            ->paginate(20);

        return [
            'success' => true,
            'message' => 'successfully',
            'data' => [
                'request_chat' => ChatRoomResource::collection($guestChats),
                'total_unread_messages' => $unreadMessages->count(),
                'unread_messages' => ChatMessageResource::collection($unreadMessages),
            ],
            'status' => 200,
        ];
    }


    public function getOrCreateChatRoom($user, $userId2)
    {
        // Find existing chat room or create a new one
        $chatRoom = ChatRoom::where(function ($query) use ($user, $userId2) {
            $query->where(function ($q) use ($user, $userId2) {
                $q->where('user_id', $user->id)
                    ->where('user_id2', $userId2);
            })
                ->orWhere(function ($q) use ($user, $userId2) {
                    $q->where('user_id', $userId2)
                        ->where('user_id2', $user->id);
                });
        })->first();

        if (!$chatRoom) {

            $user2 = User::find($userId2);
            $type = 'guest';
            if ($user->followBack($user2)) {
                $type = 'friends';
            }

            $chatRoom = ChatRoom::create([
                'user_id' => $user->id,
                'user_id2' => $userId2,
                'type' => $type,
            ]);
        }

        return $chatRoom;
    }

    public function getChatMessages($chatRoomId)
    {
        // Get messages with reacts and albums for the chat room
        return ChatMessage::where('chat_room_id', $chatRoomId)
            ->with('reacts', 'albums')
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    public function markMessagesAsSeen($checkRoom, $user)
    {
        // Mark unread messages as seen
        ChatMessage::where('chat_room_id', $checkRoom->id)
            ->where('user_id', '!=', $user->id)
            ->where('status', '!=', 'seen')
            ->update(['status' => 'seen']);
    }

    public function getUserInChatRoom($checkRoom, $user)
    {
        // Get the second user in the chat room
        return $checkRoom->user_id == $user->id
            ? User::find($checkRoom->user_id2)
            : User::find($checkRoom->user_id);
    }

    public function handleChatOpenEvent($checkRoom, $user, $user2)
    {
        // Dispatch the event to open the chat room
        try {
            $roomResource = new ChatRoomResourcePusher($checkRoom);
            event(new OpenChat($roomResource->toResponse(request())->getData()->data, $user2, $checkRoom));
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            throw $th;
        }
    }

    public function getRoomData($user2)
    {
        // Get room data and check if it has a password
        $room = Room::where('uid', $user2->now_room_uid)->first();
        return [
            'room_owner_id' => $user2->now_room_uid,
            'has_password' => $room && $room->room_pass ? true : false
        ];
    }

    public function prepareResponseData($messages, $checkRoom, $user2, $roomData)
    {
        // Prepare the data for the API response
        return [
            'messages' => ChatMessageResource::collection($messages),
            'chat_room_id' => $checkRoom->id,
            'user_now_room' => $roomData
        ];
    }


    public function deleteChatRoom($user, $userId2)
    {
        // Find the chat room
        $checkRoom = ChatRoom::where(function ($query) use ($user, $userId2) {
            $query->where('user_id', $user->id)
                ->where('user_id2', $userId2)
                ->orWhere('user_id', $userId2)
                ->where('user_id2', $user->id);
        })->first();

        if (!$checkRoom) {
            return [
                'status' => 404,
                'message' => 'Chat not Found'
            ];
        }

        // Fetch related media for response
        $midea = MessageAlbum::where('chat_room_id', $checkRoom->id)->get();
        $mideaStrings = $midea->flatMap(function ($item) {
            return [$item->file, $item->frame];
        })->toArray();

        // Delete the related chat room data
        try {
            Storage::disk('gcs')->deleteDirectory('Chat_' . env('APP_ENV') . '/chat_' . $checkRoom->id);
        } catch (\Throwable $th) {
            Log::error('Error deleting chat room storage: ' . $th->getMessage());
        }

        // Delete related records
        MessageAlbum::where('chat_room_id', $checkRoom->id)->delete();
        ChatMessage::where('chat_room_id', $checkRoom->id)->delete();
        React::where('chat_room_id', $checkRoom->id)->delete();

        // Optionally delete the chat room itself
        $checkRoom->delete();

        return [
            'status' => 200,
            'message' => 'Chat Deleted',
            'midea' => $mideaStrings
        ];
    }


    public function acceptRequest($request)
    {

        $user = $request->user();
        // Check if the chat room exists
        $checkRoom = ChatRoom::where('user_id', $request->user_id)
            ->where('user_id2', $user->id)
            ->where('type', 'guest')
            ->first();

        if (!$checkRoom) {
            return [
                'status' => 404,
                'message' => 'Chat not Found',
            ];
        }

        // Update the chat room type to 'friends'
        $checkRoom->type = 'friends';
        $checkRoom->update();

        // Get the messages related to the chat room
        $data = ChatMessage::where('chat_room_id', $checkRoom->id)
            ->with('reacts', 'albums')
            ->get();

        // Return the formatted message data
        return ChatMessageResource::collection($data);
    }

    public function sendMessages(int|string|null $userId, mixed $userIds, array $data): mixed
    {
        $message = @$data['message'];
        $url = @$data['url'];

        ChatRoom::query()
            ->select(['id', 'user_id', 'user_id2'])
            ->where(fn(Builder $q) => $q->where('user_id', $userId)->whereIn('user_id2', $userIds))
            ->orWhere(fn(Builder $q) => $q->where('user_id2', $userId)->whereIn('user_id', $userIds))
            ->chunk(400, function ($chatRooms) use (&$userIds, $userId, $message, $url) {
                $ids  = $chatRooms->pluck('user_id')->toArray();
                $ids2 = $chatRooms->pluck('user_id2')->toArray();

                $allIds = array_unique(array_merge($ids, $ids2));

                $userIds = array_diff($userIds, $allIds);

                $data = [];
                foreach ($chatRooms as $chatRoom) {
                    $userChatId = $chatRoom->user_id != $userId ? $chatRoom->user_id : $chatRoom->user_id2;
                    $data[]     = [
                        'chat_room_id' => $chatRoom->id,
                        'user_id'      => $userChatId,
                        'message'      => $message,
                        'status'       => 'received',
                        'type'         => 'img',
                        'file'         => $url,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                ChatMessage::insert($data);
            });
        return $userIds;
    }


    public function createNewChatRooms(mixed $userIds, int|string|null $userId): void
    {
        $data = [];
        // create chat room and store message
        foreach ($userIds as $userIdDiff) {

            $data[] = [
                'user_id'  => $userId,
                'user_id2' => $userIdDiff,
            ];
        }
        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $chunk) {
            ChatRoom::query()->insert($chunk);
        }
    }
}
