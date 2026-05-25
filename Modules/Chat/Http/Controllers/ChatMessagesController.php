<?php

namespace Modules\Chat\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Modules\Chat\Events\Chat;
use Modules\Chat\Events\Conversation;
use App\Http\Controllers\Controller;
use Modules\Chat\Events\OpenChat;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use App\Models\BlockList;
use Modules\Chat\Entities\BlockUser;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Entities\MessageAlbum;
use Modules\Chat\Entities\MessageReplay;
use App\Models\User;
use Modules\Chat\Traits\FfmpegTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\Common;
use App\Models\BlackList;
use App\Models\Config;
use DB;
use Modules\Chat\Events\CardDeleteMessage;
use Modules\Chat\Events\DeleteMessage;
use Modules\Chat\Http\Services\ChatService;
use Modules\Chat\Http\Services\MessageService;
use Modules\Chat\Http\Requests\ChatStoreRequest;
use Modules\Chat\Http\Requests\DeleteForMeRequest;
use Modules\Chat\Http\Requests\DeleteMessagesRequest;
use Modules\Chat\Http\Requests\UpdateMessageRequest;

class ChatMessagesController extends Controller
{
    use FfmpegTrait;

    public function __construct(public ChatService $chatService, public MessageService $messageService) {}



    public function store(ChatStoreRequest $request)
    {

        $user = $request->user();

        if ($this->chatService->isUserBlocked($request->user()->id, $request->user_id)) {
            return response()->json([
                'status' => 403,
                'message' => "Unauthorized Block Condition"
            ], 403);
        }

        $chatRoom = $this->chatService->findChatRoomBetweenUsers($user->id, $request->user_id);

        if (!$chatRoom) {
            return response()->json([
                'status' => 404,
                'message' => 'Chat not Found',
            ], status: 404);
        }

        $total_message = $this->chatService->countMessagesByUserInRoom($chatRoom->id, $user->id);

        $totalDistinctUsers = $this->chatService->countDistinctUsersInRoom($chatRoom->id);

        $maxMessage = \Cache::rememberForever('max_message', function () {
            $setting =   Config::where('name', 'max_message')->first();
            return $setting?->value ?? 3;
        });

        if ($chatRoom->type == 'guest' && $total_message >= $maxMessage && $totalDistinctUsers < 2) {
            return response()->json([
                'status' => 429,
                'message' => __("limitChatMessage"),
            ], 429);
        }

        if ($chatRoom->user_id != $user->id) {
            $user2 = User::withoutAppends()->find($chatRoom->user_id);
        } else {
            $user2 = User::withoutAppends()->find($chatRoom->user_id2);
        }

        //Files Validations
        if ($request->hasFile('file')) {
            $validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'mp4', 'mp3', 'wav', 'pdf'];
            foreach ($request->file('file') as $file) {
                if (!$this->isValidFileExtension($file, $validExtensions)) {
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


        // //add status for message
        // if($user2->online == 1 && $user2->current_room_chat == $check_room->id )
        // {
        //     $message->status = 'seen';
        //     $message->update();
        // }
        // else if($user2->online == 1)
        // {
        //     $message->status = 'received';
        //     $message->update();
        // }
        // else{
        //     $tokens_notfacion[] = DB::table('users')->where('id', $user2->id)->value('notification_id');
        //     $title=$user->name;
        //     $body= $message->message ;
        //     Common::send_firebase_notification($tokens_notfacion,$title,$body,messageType: 'message');
        // }

        //insert files to database

        $this->messageService->handleFileUpload($request, $chatRoom, $message, $user);

        $response = $this->messageService->handleMessage($request, $message, $user, $user2, $chatRoom);

        // Clear cached chat rooms for both users so the list reflects the new message
        \Cache::forget("chat_rooms_{$user->id}");
        \Cache::forget("chat_rooms_{$user2->id}");

        //add status for message
        if ($totalDistinctUsers >= 2) {
            $chatRoom->type = 'friend';
        }


        try {
            // return $user2;
            event(new Conversation($response['message_resource']->toResponse(request())->getData()->data, $user2, $response['room_resource']));
            event(new Chat($response['room_resource']->toResponse(request())->getData()->data, $user2));
            event(new OpenChat($response['room_resource']->toResponse(request())->getData()->data, $user2 ?? $user, $chatRoom, false));
        } catch (\Throwable $e) {
        }

        if (!$user2->current_room_chat !=  $chatRoom->id){
            $this->messageService->sendNotification($user2, $message);
        }

        return [
            'message' =>    $response['message_resource'],
            'card' =>  new ChatRoomResource($chatRoom)
        ];
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

        // Clear cached chat rooms for both users
        $msg = \Modules\Chat\Entities\ChatMessage::find($request->message_id);
        if ($msg) {
            $chatRoom = \Modules\Chat\Entities\ChatRoom::find($msg->chat_room_id);
            if ($chatRoom) {
                \Cache::forget("chat_rooms_{$chatRoom->user_id}");
                \Cache::forget("chat_rooms_{$chatRoom->user_id2}");
            }
        }

        return new ChatMessageResource($response);
    }

    public function deleteForAll(DeleteMessagesRequest $request)
    {

        $response = $this->chatService->deleteMessages($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        // Clear cached chat rooms for both users
        $firstMsg = \Modules\Chat\Entities\ChatMessage::find($request->id[0]);
        if ($firstMsg) {
            $chatRoom = \Modules\Chat\Entities\ChatRoom::find($firstMsg->chat_room_id);
            if ($chatRoom) {
                \Cache::forget("chat_rooms_{$chatRoom->user_id}");
                \Cache::forget("chat_rooms_{$chatRoom->user_id2}");
            }
        }

        return response()->json($response);
    }

    public function deleteForMe(DeleteForMeRequest $request)
    {

        $response = $this->chatService->deleteForUser($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        // Clear cached chat rooms for the current user
        \Cache::forget("chat_rooms_{$request->user()->id}");

        return response()->json($response);
    }
}
