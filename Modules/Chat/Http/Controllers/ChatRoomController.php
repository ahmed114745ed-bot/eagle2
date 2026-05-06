<?php

namespace Modules\Chat\Http\Controllers;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
// use Modules\Chat\Entities\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Chat\Http\Resources\ChatRoomResource;
use Modules\Chat\Http\Services\ChatRoomService;
use Modules\Chat\Http\Services\ChatService;

class ChatRoomController extends Controller
{

    public function __construct(public ChatRoomService $chatRoomService, public ChatService $chatService)
    {

    }

    public function users_list(){
        return Common::get_users_list();
    }
    public function inviteRoom(Request $request)
    {

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
        $uuid = $request->keyword;
        $user = $request->user();

        // Cache for 2 minutes (120 seconds) to fix 4.3s latency reported in DevOps report
        // Short TTL because chat data changes frequently
        // Skip cache if searching by keyword (uuid)
        if ($uuid) {
            $response = $this->chatRoomService->getChatRooms($user, $uuid);
        } else {
            $response = \Cache::remember("chat_rooms_{$user->id}", 120, function () use ($user, $uuid) {
                return $this->chatRoomService->getChatRooms($user, $uuid);
            });
        }

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

    public function guestChat(Request $request)
    {
        $response = $this->chatRoomService->getGUestChatRooms($request->user());


        if (!$response['success']) {
            return response()->json($response['message'], $response['status']);
        }

        return Common::apiResponse(
            1,
            $response['message'],
            $response['data'],
            $response['status'],
            '',
            'request_chat'
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

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $checkRoom = $this->chatRoomService->getOrCreateChatRoom($user, $request->user_id);

        // Update user's current room chat

        $user->current_room_chat = $checkRoom->id;
        $user->update();
        // Retrieve and paginate chat messages
        $messages = $this->chatRoomService->getChatMessages($checkRoom->id, $request, $user);

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

    public function cursor(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => ['sometimes', 'string', Rule::in(['new', 'old'])],
            'message_id' => ['sometimes', 'integer']
        ]);

        $user = $request->user();
        $checkRoom = $this->chatRoomService->getOrCreateChatRoom($user, $request->user_id);

        $user->current_room_chat = $checkRoom->id;
        $user->update();
        $messages = $this->chatRoomService->getChatMessages($checkRoom->id, $request,$user);
     //   dd( $messages->toArray());

        $this->chatRoomService->markMessagesAsSeen($checkRoom, $user);

        $user2 = $this->chatRoomService->getUserInChatRoom($checkRoom, $user);

        $this->chatRoomService->handleChatOpenEvent($checkRoom, $user, $user2);

        $roomData = $this->chatRoomService->getRoomData($user2);

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
    //         return $th->getMessage();
    //     }

    //     return [
    //         'messages' => ChatMessageResource::collection($data),
    //         'chat_room_id' => $check_room->id
    //     ];

    // }

    public function destroy(Request $request, $id)
    {
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

        $response = $this->chatRoomService->acceptRequest($request);

        return response()->json($response);

    }

    /**
     * @param int|string|null $userId
     * @param mixed $userIds
     * @param mixed $message
     * @return mixed
     */

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
