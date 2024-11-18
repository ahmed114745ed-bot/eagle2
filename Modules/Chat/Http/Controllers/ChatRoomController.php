<?php

namespace Modules\Chat\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
// use Modules\Chat\Entities\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Chat\Http\Services\ChatRoomService;

class ChatRoomController extends Controller
{

    public function __construct(public ChatRoomService $chatRoomService)
    {

    }
    public function inviteRoom(Request $request)
    {
       /*  $userId = \Auth::id();

        $message = $request->message;
        $imageUrl = $request->image_url;

        $data = [
            'message' => $message,
            'url' => $imageUrl
        ];
        if ($request->type == 'all') {
            $except = $request->except_ids;
            if ($except != '') $except = explode(',', $except);
            else $except = [];

            User::query()
                ->select('id')
                ->whereHas('followers', fn($q) => $q->where('user_id', $userId))
                ->whereHas('followeds', fn($q) => $q->where('followed_user_id', $userId))
                ->whereNotIn('id', $except)
                ->chunk(400, function ($userIds) use ($userId, $data) {
                    $userIds = $userIds->pluck('id')->toArray();
                    $this->sendMessageToUsers($userId, $userIds, $data);
                });
            return Common::apiResponse(true, __('success'));
        } else {
            $userIds = $request->users;
            if ($userIds != '') $userIds = explode(',', $userIds);
            else $userIds = [];
        }


        $this->sendMessageToUsers($userId, $userIds, $data);

        return Common::apiResponse(true, __('success')); */

        $userId = auth()->id();

        $data = [
            'message' => $request->message,
            'url'     => $request->image_url,
        ];

        $type = $request->type;
        $userIds = $request->users ? explode(',', $request->users) : [];
        $exceptIds = $request->except_ids ? explode(',', $request->except_ids) : [];

        $this->chatRoomService->handleInvite($data, $userId, $type, $userIds, $exceptIds);

        return Common::apiResponse(true, __('success'));

    }
    public function find_user(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1|max:255',
        ]);

        $data = $this->chatRoomService->findUsersByName($request->name);

        return response()->json($data);

    }

    public function index(Request $request)
    {

        /* $user = User::with('chats')->find($request->user()->id);
        if (!$user) {
            return response()->json('user not found', 200);
        }
        //get top chats
        $chats = $user->chats->pluck('id')->toArray();

        //get user chats
        $friends = ChatRoom::WhereHas('messages')->select('chat_rooms.*', DB::raw('(SELECT MAX(created_at) FROM chat_messages WHERE chat_messages.chat_room_id = chat_rooms.id) AS last_message_created_at'))
            ->where(function ($query) use ($chats, $user) {
                $query->where('chat_rooms.id', 'not Like', $chats)
                    ->where('chat_rooms.user_id', $user->id)
                    ->where('chat_rooms.type', 'friends');
            })
            ->orWhere(function ($query) use ($chats, $user) {
                $query->where('chat_rooms.id', 'not Like', $chats)
                    ->where('chat_rooms.user_id2', $user->id)
                    ->where('chat_rooms.type', 'friends');
            })
            ->groupBy(['chat_rooms.id', 'chat_rooms.user_id', 'chat_rooms.user_id2', 'chat_rooms.type', 'user_1_deleted', 'user_2_deleted', 'created_at', 'updated_at'])
            ->orderByDesc('last_message_created_at')
            ->paginate(20);

        //get chat requests
        $guest = ChatRoom::WhereHas('messages')->select('chat_rooms.*')->where('chat_rooms.user_id2', $user->id)->where('chat_rooms.type', 'guest')->with('messages')
            ->join('chat_messages', 'chat_rooms.id', '=', 'chat_messages.chat_room_id')
            ->orderBy('chat_messages.id', 'desc')
            ->paginate(20);



        $chats_id = ChatRoom::where('user_id', $user->id)->orWhere('user_id2', $user->id)->get()->pluck('id')->toArray();
        $total_unread =  ChatMessage::whereIn('chat_room_id', $chats_id)->where('user_id', 'not Like', $user->id)->where('status', 'not Like', 'seen')->paginate(20);
        $data = [
            'top_chats' => ChatRoomResource::collection($user->chats),
            'chat' => ChatRoomResource::collection($friends),
            'request_chat' => ChatRoomResource::collection($guest),
            'total_unread_messages' => $total_unread->count(),
            'unread_messages' => ChatMessageResource::collection($total_unread),
        ];
        return Common::apiResponse(1, 'successfully', $data, 200, '', 'chat'); */

        $response = $this->chatRoomService->getChatRooms($request->user());

        if (!$response['success']) {
            return response()->json($response['message'], $response['status']);
        }

        return Common::apiResponse(
            1,
            $response['message'],
            $response['data'],
            $response['status'],
            '',
            'chat'
        );
    }
    public function close_Chat(Request $request)
    {
        $user = User::find($request->user()->id);
        $user->current_room_chat  = null;
        $user->save();
        return 200;
    }

    public function store(Request $request)
    {
        /* $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $user = $request->user();
        // $follow_check = Follow::where('user_id', $user->id)->where('followed_user_id', $request->user_id)
        //     ->orWhere('user_id', $request->user_id)->where('followed_user_id', $user->id)->first();

        $check_room = ChatRoom::where('user_id', $user->id)->where('user_id2', $request->user_id)
            ->orWhere('user_id', $request->user_id)->where('user_id2', $user->id)->first();
        if (!$check_room) {
            $check_room = new ChatRoom();
            $check_room->user_id = $user->id;
            $check_room->user_id2 = $request->user_id;
            // if (!$follow_check) {
            //     $check_room->type = 'guest';
            // }
            $check_room->save();
        }
        $user->current_room_chat = $check_room->id;
        $user->update();
        $data = ChatMessage::where('chat_room_id', $check_room->id)->with('reacts', 'albums')->orderBy('id', 'desc')->paginate(15);

        //        $total_unread =  ChatMessage::where('chat_room_id', $check_room->id)->where('user_id','not Like',$user->id)->where('status','not Like','seen')->get();
        $room_resource =  new ChatRoomResourcePusher($check_room);
        //        dispatch(new ReciveChatMessagejob($total_unread , 'seen'));

        // update to seen

        ChatMessage::where('chat_room_id', $check_room->id)->where('user_id', 'not Like', $user->id)->where('status', 'not Like', 'seen')
            ->update(['status' => 'seen']);

        if ($check_room->user_id == $user->id) {
            $user2 = User::find($check_room->user_id2);
        } else {
            $user2 = User::find($check_room->user_id);
        }
        try {
            event(new OpenChat($room_resource->toResponse(request())->getData()->data, $user2, $check_room));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return $th->getMessage();
        }

        // this code to get user now room id and password of room
        $pass_status = false;
        $now_room = Room::query()->where('uid', $user2->now_room_uid)->first();
        if ($now_room) {
            if ($now_room->room_pass) {
                $pass_status = true;
            }
        }
        $dataResource = [
            'messages' =>  ChatMessageResource::collection($data),
            'chat_room_id' => $check_room->id,
            'user_now_room' => [
                'room_owner_id' => $user2->now_room_uid,
                'has_password'  =>  $pass_status
            ]
        ];
        return Common::apiResponse(1, 'successfully', $dataResource, 200, '', 'messages'); */

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $checkRoom = $this->chatRoomService->getOrCreateChatRoom($user, $request->user_id);

        // Update user's current room chat

        $user->current_room_chat = $checkRoom->id;
        $user->update();
        // Retrieve and paginate chat messages
        $messages = $this->chatRoomService->getChatMessages($checkRoom->id);

        // Mark unread messages as seen
        $this->chatRoomService->markMessagesAsSeen($checkRoom, $user);

        // Find the second user in the chat room
        $user2 = $this->chatRoomService->getUserInChatRoom($checkRoom, $user);

        // Handle chat opening event
        $this->chatRoomService->handleChatOpenEvent($checkRoom, $user, $user2);

        // Check if room has a password
        $roomData = $this->chatRoomService->getRoomData($user2);

        // Prepare data for response
        $responseData = $this->chatRoomService->prepareResponseData($messages, $checkRoom, $user2, $roomData);

        return Common::apiResponse(1, 'successfully', $responseData, 200, '', 'messages');

    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //     ]);
    //     $user = $request->user();

    //     $check_room = ChatRoom::where(function ($query) use ($user, $request) {
    //         $query->where('user_id', $user->id)->where('user_id2', $request->user_id);
    //     })->orWhere(function ($query) use ($user, $request) {
    //         $query->where('user_id', $request->user_id)->where('user_id2', $user->id);
    //     })->first();

    //     if (!$check_room) {
    //         $check_room = new ChatRoom();
    //         $check_room->user_id = $user->id;
    //         $check_room->user_id2 = $request->user_id;
    //         $check_room->save();
    //     }
    //     $user->current_room_chat = $check_room->id;
    //     $user->update();

    //     $data = ChatMessage::where('chat_room_id', $check_room->id)
    //         ->with('reacts', 'albums')
    //         ->orderBy('id', 'desc')
    //         ->paginate(15);

    //     $total_unread = ChatMessage::where('chat_room_id', $check_room->id)
    //         ->where('user_id', '!=', $user->id)
    //         ->where('status', '!=', 'seen')
    //         ->get();

    //     dispatch(new ReciveChatMessagejob($total_unread->pluck('id'), 'seen'));

    //     if ($check_room->user_id == $user->id) {
    //         $user2 = User::find($check_room->user_id2);
    //     } else {
    //         $user2 = User::find($check_room->user_id);
    //     }

    //     try {
    //         event(new OpenChat(['chat_room_id' => $check_room->id,'chat_room_type' => $check_room->type,'user2_profile' => $user2->profile ?? null
    //         ], $user2->id));
    //     } catch (\Throwable $th) {
    //         Log::info($th->getMessage());
    //         return $th->getMessage();
    //     }

    //     return [
    //         'messages' => ChatMessageResource::collection($data),
    //         'chat_room_id' => $check_room->id
    //     ];

    // }

    public function destroy(Request $request, $id)
    {

        /* $user = $request->user();
        $check_room = ChatRoom::where('user_id', $user->id)->where('user_id2', $id)->orWhere('user_id', $id)->where('user_id2', $user->id)->first();
        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        $midea = MessageAlbum::where('chat_room_id', $check_room->id)->get();
        $mideaStrings = [];
        foreach ($midea as $key => $item) {
            $mideaStrings[] =  $item->file;
            $mideaStrings[] = $item->frame;
        }
        // if ($check_room) {
        //     $check_room->delete();
        // }
        try {
            Storage::disk('gcs')->deleteDirectory('Chat_' . env('APP_ENV') . '/chat_' . $item->chat_room_id);
        } catch (\Throwable $th) {
            //throw $th;
        }
        MessageAlbum::where('chat_room_id', $check_room->id)->delete();
        ChatMessage::where('chat_room_id', $check_room->id)->delete();
        React::where('chat_room_id', $check_room->id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Chat Deleted',
            'midea' => $mideaStrings
        ]); */


        $user = $request->user();

        // Call the service method to handle chat room deletion
        $response = $this->chatRoomService->deleteChatRoom($user, $id);

        return response()->json($response);
    }

    public function accept_request(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        /* $user = $request->user();
        $check_room = ChatRoom::where('user_id', $request->user_id)->where('user_id2', $user->id)->where('type', 'guest')->first();
        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        $check_room->type = 'friends';
        $check_room->update();
        $data = ChatMessage::where('chat_room_id', $check_room->id)->with('reacts', 'albums')->get();
        return ChatMessageResource::collection($data); */

        $response = $this->chatRoomService->acceptRequest($request);

        return response()->json($response);

    }

    /**
     * @param int|string|null $userId
     * @param mixed $userIds
     * @param mixed $message
     * @return mixed
     */
/*     public function sendMessages(int|string|null $userId, mixed $userIds, array $data): mixed
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
    } */

    /**
     * @param mixed $userIds
     * @param int|string|null $userId
     * @return void
     */
/*     public function createNewChatRooms(mixed $userIds, int|string|null $userId): void
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
    } */

    /**
     * @param int|string|null $userId
     * @param mixed $userIds
     * @param mixed $message
     * @return void
     */
    /* public function sendMessageToUsers(int|string|null $userId, mixed $userIds, array $message): void
    {
        $timeZone = request()->hasHeader('tz') ? request()->header()['tz'][0] : 'UTC';
        dispatchJobToQueue(new SendMessageToAllUsers($userId, $userIds, $message, timezone: $timeZone), 'heavyProcessing');
        // $userIds = $this->sendMessages($userId, $userIds, $message);

        // $this->createNewChatRooms($userIds, $userId);

        // $this->sendMessages($userId, $userIds, $message);
    } */
}
