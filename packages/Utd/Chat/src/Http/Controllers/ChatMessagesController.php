<?php

namespace Utd\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Throwable;
use Utd\Chat\Events\Chat;
use Utd\Chat\Events\Conversation;
use Utd\Chat\Events\OpenChat;
use Utd\Chat\Http\Requests\ChatStoreRequest;
use Utd\Chat\Http\Requests\DeleteForMeRequest;
use Utd\Chat\Http\Requests\DeleteMessagesRequest;
use Utd\Chat\Http\Requests\UpdateMessageRequest;
use Utd\Chat\Http\Resources\ChatMessageResource;
use Utd\Chat\Http\Resources\ChatRoomResource;
use Utd\Chat\Http\Services\ChatService;
use Utd\Chat\Http\Services\MessageService;
use Utd\Chat\Traits\FfmpegTrait;

class ChatMessagesController extends Controller
{
    use FfmpegTrait;

    public function __construct(public ChatService $chatService, public MessageService $messageService) {}

    public function store(ChatStoreRequest $request)
    {

        $user = $request->user();

        if ($this->chatService->isUserBlocked($request->user()->id, $request->user_id)) {
            return response()->json([
                'status' => 404,
                'message' => 'Unauthorized Block Condition',
            ], 404);
        }

        $chatRoom = $this->chatService->findChatRoomBetweenUsers($user->id, $request->user_id);

        if (! $chatRoom) {
            return response()->json([
                'status' => 404,
                'message' => 'Chat not Found',
            ], status: 404);
        }

        $total_message = $this->chatService->countMessagesByUserInRoom($chatRoom->id, $user->id);

        $totalDistinctUsers = $this->chatService->countDistinctUsersInRoom($chatRoom->id);

        $maxMessage = Cache::rememberForever('max_message', function () {
            $setting = Config::where('name', 'max_message')->first();

            return $setting?->value ?? 0;
        });

        if ($chatRoom->type === 'guest' && $total_message >= $maxMessage && $totalDistinctUsers < 2) {
            return response()->json([
                'status' => 404,
                'message' => 'You have reached the limit for sending messages',
            ], 404);
        }

        if ($chatRoom->user_id !== $user->id) {
            $user2 = User::withoutAppends()->find($chatRoom->user_id);
        } else {
            $user2 = User::withoutAppends()->find($chatRoom->user_id2);
        }

        // Files Validations
        if ($request->hasFile('file')) {
            $validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'mp4', 'mp3', 'wav', 'pdf'];
            foreach ($request->file('file') as $file) {
                if (! $this->isValidFileExtension($file, $validExtensions)) {
                    return $this->fileValidationErrorResponse();
                }
            }
        }

        $messageData = [
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ];

        $message = $this->chatService->createChatMessage($messageData);

        // insert files to database

        $this->messageService->handleFileUpload($request, $chatRoom, $message, $user);

        $response = $this->messageService->handleMessage($request, $message, $user, $user2, $chatRoom);

        // add status for message
        if ($totalDistinctUsers >= 2) {
            $chatRoom->type = 'friend';
        }

        //    \Log::info('room_resource ', ['room_resource' => $response['room_resource']]);
        //    \Log::info('room_resource ', ['room_resource' => $response['room_resource'] , 'room req' => $response['message_resource']->toResponse(request())->getData()->data]);

        try {
            // return $user2;
            event(new Conversation($response['message_resource']->toResponse(request())->getData()->data, $user2, $response['room_resource']));
            event(new Chat($response['room_resource']->toResponse(request())->getData()->data, $user2));
            event(new OpenChat($response['room_resource']->toResponse(request())->getData()->data, $user2 ?? $user, $chatRoom, false));
        } catch (Throwable $e) {
        }

        if (! $user2->current_room_chat !== $chatRoom->id) {
            $this->messageService->sendNotification($user2, $message);
        }

        return [
            'message' => $response['message_resource'],
            'card' => new ChatRoomResource($chatRoom),
        ];
    }

    public function update(UpdateMessageRequest $request)
    {

        $user = $request->user();
        $response = $this->chatService->updateMessage(
            $request->message_id,
            $request->message,
            $user
        );

        if (isset($response['status']) && $response['status'] === 404) {
            return response()->json($response, 404);
        }

        return new ChatMessageResource($response);
    }

    public function deleteForAll(DeleteMessagesRequest $request)
    {

        $response = $this->chatService->deleteMessages($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        return response()->json($response);
    }

    public function deleteForMe(DeleteForMeRequest $request)
    {

        $response = $this->chatService->deleteForUser($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        return response()->json($response);
    }

    private function isValidFileExtension($file, $validExtensions)
    {
        $extension = $file->getClientOriginalExtension();

        return in_array($extension, $validExtensions);
    }

    private function fileValidationErrorResponse()
    {
        return response()->json([
            'status' => 404,
            'message' => "File doesn't match our records",
        ], 404);
    }
}
