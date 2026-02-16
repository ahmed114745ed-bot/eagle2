<?php

namespace Utd\Chat\Http\Controllers;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Utd\Chat\Http\Services\ChatRoomService;
use Utd\Chat\Http\Services\ChatService;

class ChatRoomController extends Controller
{
    public function __construct(public ChatRoomService $chatRoomService, public ChatService $chatService) {}

    public function users_list()
    {
        return Common::get_users_list();
    }

    public function inviteRoom(Request $request)
    {

        $userId = auth()->id();

        $data = [
            'message' => $request->message,
            'url' => $request->image_url,
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
        $response = $this->chatRoomService->getChatRooms($request->user(), $uuid);

        if (! $response['success']) {
            return response()->json($response['message'], $response['status']);
        }

        //  \Log::info('ChatRooms Response', [
        //         'success' => $response['success'],
        //         'status'  => $response['status'],
        //         'data' => $response['data'],
        //         'message' => $response['message'],
        //         'rooms_count' => isset($response['data']) ? count($response['data']) : 0,
        //     ]);
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

        if (! $response['success']) {
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
        $user->current_room_chat = null;
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
            'message_id' => ['sometimes', 'integer'],
        ]);

        $user = $request->user();
        $checkRoom = $this->chatRoomService->getOrCreateChatRoom($user, $request->user_id);

        $user->current_room_chat = $checkRoom->id;
        $user->update();
        $messages = $this->chatRoomService->getChatMessages($checkRoom->id, $request, $user);
        //   dd( $messages->toArray());

        $this->chatRoomService->markMessagesAsSeen($checkRoom, $user);

        $user2 = $this->chatRoomService->getUserInChatRoom($checkRoom, $user);

        $this->chatRoomService->handleChatOpenEvent($checkRoom, $user, $user2);

        $roomData = $this->chatRoomService->getRoomData($user2);

        $responseData = $this->chatRoomService->prepareResponseData($messages, $checkRoom, $user2, $roomData);

        return Common::apiResponse(1, 'successfully', $responseData, 200, '', 'messages');

    }

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
}
