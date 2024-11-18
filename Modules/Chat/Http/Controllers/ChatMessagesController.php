<?php

namespace Modules\Chat\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Modules\Chat\Events\Chat;
use Modules\Chat\Events\Conversation;
use App\Http\Controllers\Controller;
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

        /* $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:255',
            'message_id' => 'nullable|exists:chat_messages,id',
        ]); */

        \Log::info('chat response : ' . json_encode($request->all()));
        $user = $request->user();

        if ($this->chatService->isUserBlocked($request->user()->id, $request->user_id)) {
            return response()->json([
                'status' => 404,
                'message' => "Unauthorized Block Condition"
            ], 404);
        }

        $chatRoom = $this->chatService->findChatRoomBetweenUsers($user->id, $request->user_id);

        if (!$chatRoom) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }

        $total_message = $this->chatService->countMessagesByUserInRoom($chatRoom->id, $user->id);

        if ($chatRoom->type == 'guest' && $total_message >= 3) {
            return response()->json([
                'status' => 404,
                'status' => 'unauthorized',
            ], 404);
        }

        /* $check =BlackList::where("user_id", $request->user()->id)->where("from_uid", $request->user_id)
        ->orwhere("user_id", $request->user_id)->where("from_uid",$request->user()->id)->first();
        if($check)
        {
            return response()->json([
                'status' => 404,
                'message' => "Unauthorized Block Condition"
            ],404);
        }

        $check_room = ChatRoom::where('user_id', $user->id)->where('user_id2', $request->user_id)
            ->orWhere('user_id', $request->user_id)->where('user_id2', $user->id)->first();

        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        $total_message = ChatMessage::where('chat_room_id',$check_room->id)->where('user_id',$user->id)->count();
        if($check_room->type == 'guest' && $total_message >=3 ){
            return response()->json([
                'status' => 404,
                'status' => 'unauthorized',
            ], 404);
        } */

        //get user 2
        if ($chatRoom->user_id == $user->id) {
            $user2 = User::withoutAppends()->find($chatRoom->user_id2);
        } else {
            $user2 = User::withoutAppends()->find($chatRoom->user_id);
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

        /* if ($request->hasFile('file')) {
            $images_extensions = ['jpeg', 'jpg', 'png','gif','mp4','mp3','wav','pdf'];
            foreach ( $request->file('file') as $file) {
                $extension = $file->getClientOriginalExtension();
                \Log::info('This is file extension : ' . json_encode($extension) . ' This is file name  : ' . json_encode($file->getFileInfo()->getExtension()));

                $check = in_array($extension, $images_extensions);
                if (!$check ) {
                    return response()->json([
                        'status' => 404,
                        'message' => "File Doesn't Match our Records",
                    ],404);
                }
            }
        } */

        $messageData = [
            'chat_room_id' => $chatRoom->id,
            'user_id' => $user->id,
            'message' => $request->message,
        ];

        $message = $this->chatService->createChatMessage($messageData);


       /*  $message = new ChatMessage();
        $message->chat_room_id = $check_room->id;
        $message->user_id = $user->id;
        $message->message = $request->message;
        $message->save(); */


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

        /* if ($request->hasFile('file')) {
            $files = $request->file('file');
            $images_extensions = ['jpeg', 'jpg', 'png'];
            $count = count($files);

            if ($count == 1) {
                $extension = $files[0]->extension();
                $check = in_array($extension, $images_extensions);
                $file = $request->file[0];

                //create album
                $album = new MessageAlbum();
                $album->chat_room_id = $chatRoom->id;
                $album->chat_message_id = $message->id;
                $album->user_id = $user->id;

                if ($check) {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                    $album->file =  $file_name;
                    $album->type =  'img';
                    $album->save();

                    $message->type = 'img';
                    $message->update();
                } else if ($extension == 'gif') {
                    $album->file =  $file->getClientOriginalName();
                    $album->type =  'gif';
                    $album->save();

                    $message->type = 'gif';
                    $message->message = null;
                    $message->update();
                } else if ($extension == 'mp4') {
                    // $file_name =  Str::uuid().'chat.' .$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name =   Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);

                    $name =  pathinfo($file_name, PATHINFO_FILENAME);

                    $album->file =  $file_name;
                    $album->type =  'video';
                    $videoPath = $file_name;
                    $thumbnailPath =  'Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id . '/' . $name . '.jpg';

                    try {
                        $this->extract_frame($videoPath, $thumbnailPath);
                    } catch (\Throwable $e) {
                        return $e->getMessage();
                    }
                    $album->frame =  $thumbnailPath;
                    $album->save();

                    $message->type = 'video';
                    $message->message = null;
                    $message->update();
                } else if ($extension == 'mp3' ||  $extension == 'wav' ||  $extension == 'm4a' ||  $extension == 'aac') {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name =  Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                    $album->file =  $file_name;
                    $album->type =  'voice';
                    $album->save();

                    $message->type = 'voice';
                    $message->message = null;
                    $message->update();
                } else if ($extension == 'pdf') {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                    $album->file =  $file_name;
                    $album->type =  'file';
                    $album->save();

                    $message->type = 'file';
                    $message->message = null;
                    $message->update();
                }
            } else {
                foreach ($files as $file) {
                    $extension = $file->extension();
                    $check = in_array($extension, $images_extensions);

                    $message->type = 'album';
                    $message->update();

                    //add album
                    $album = new MessageAlbum();
                    $album->chat_room_id = $chatRoom->id;
                    $album->chat_message_id = $message->id;
                    $album->user_id = $user->id;

                    if ($check) {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                        $album->file =  $file_name;
                        $album->type =  'img';
                        $album->save();
                    } else if ($extension == 'gif') {
                        $album->file =  $file->getClientOriginalName();
                        $album->type =  'gif';
                        $album->save();



                        $album->type =  'gif';
                        $album->save();
                    } else if ($extension == 'mp4') {
                        // $file_name =  Str::uuid().'chat.' .$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                        $name =  pathinfo($file_name, PATHINFO_FILENAME);

                        $album->file =  $file_name;
                        $album->type =  'video';
                        $videoPath = $file_name;
                        $thumbnailPath =  'Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id . '/' . $name . '.jpg';
                        try {
                            $this->extract_frame($videoPath, $thumbnailPath);
                        } catch (\Throwable $e) {
                            return $e->getMessage();
                        }
                        $album->frame =  $thumbnailPath;
                        $album->save();
                    } else if ($extension == 'mp3' ||  $extension == 'wav' ||  $extension == 'm4a' ||  $extension == 'aac') {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                        $album->file =  $file_name;
                        $album->type =  'voice';
                        $album->save();
                    } else if ($extension == 'pdf') {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
                        $album->file =  $file_name;
                        $album->type =  'file';
                        $album->save();
                    }
                }
            }
        } */
        $response = $this->messageService->handleMessage($request, $message, $user, $user2, $chatRoom);

        //add status for message
/*         if ($user2->online == 1 && $user2->current_room_chat == $chatRoom->id) {
            $message->status = 'seen';
            $message->update();
        } else if ($user2->online == 1) {
            $message->status = 'received';
            $message->update();
        }


        if ($user2->is_logout != 1) {
            $tokens_notfacion[] = DB::table('users')->where('id', $user2->id)->value('notification_id');
            $title = $user->name;
            $body = $message->message;
            $type = $message->type ?? 'text';
            Common::send_firebase_notification($tokens_notfacion, $title, $body, messageType: $type);
        }

        //Replay Message
        if ($request->message_id) {
            $data = new MessageReplay();
            $data->message_id = $message->id;
            $data->from_message_id  = $request->message_id;
            $data->save();
        }

        $data = ChatMessage::find($message->id);

        $message_resource = new ChatMessageResource($data);
        $room_resource =  new ChatRoomResourcePusher($chatRoom); */

        // return $user2;
        event(new Conversation($response['message_resource']->toResponse(request())->getData()->data, $user2, $response['room_resource']));
        event(new Chat($response['room_resource']->toResponse(request())->getData()->data, $user2));
        return [
            'message' =>    $response['message_resource'],
            'card' =>  new ChatRoomResource($chatRoom)
        ];
    }

    private function isValidFileExtension($file, $validExtensions)
    {
        $extension = $file->getClientOriginalExtension();
        \Log::info('This is file extension: ' . json_encode($extension) . ' This is file name: ' . json_encode($file->getClientOriginalName()));

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
        /* $request->validate([
            'message_id' => 'required|exists:chat_messages,id',
        ]); */


        /* $message = ChatMessage::find($request->message_id);

        $chat_room_id = ChatRoom::find($message->chat_room_id)->id;
        $user = $request->user();

        if (!$message || !$chat_room_id || $message->user_id !== $user->id) {
            return response()->json([
                'status' => 404,
                'message' => 'unAuthorized',
            ], 404);
        }


        $date = Carbon::now()->format('Y-m-d H:i:s');
        $created_at = Carbon::parse($message->created_at)->addMinutes(15)->format('Y-m-d H:i:s');
        if ($date >  $created_at) {
            return response()->json([
                'status' => 404,
                'message' => 'Deletion is permissible within a 15-minute  after sending.',
            ], 404);
        }


        $message->message = $request->message;
        $message->update();
        $data = ChatMessage::find($message->id);
        return new ChatMessageResource($data); */

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
        /* $chat_room_id = 0;
        $user = $request->user();
        $ids = [];
        if (!$request->id) {
            return response()->json([
                'status' => 404,
                'message' => 'missing parameter',
            ], 404);
        }
        foreach ($request->id as $id) {
            $ids[] = (int)$id;
            $message = ChatMessage::find($id);
            if (!$message) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Message not found',
                ], 404);
            }
            if ($message->user_id !== $user->id) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Delete Forbidden ',
                ], 404);
            }

            $date = Carbon::now()->format('Y-m-d H:i:s');
            $created_at = Carbon::parse($message->created_at)->addDay(1)->format('Y-m-d H:i:s');
            if ($date >  $created_at) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Deletion is permissible within a Day  after sending.',
                ], 404);
            }

            $message->delete();
            $chat_room_id = $message->chat_room_id;
        }
        $check_room = ChatRoom::find($chat_room_id);
        if ($check_room->user_id == $user->id) {
            $user2 = User::find($check_room->user_id2);
        } else {
            $user2 = User::find($check_room->user_id);
        }

        $room_resource =  new ChatRoomResourcePusher($check_room);
        event(new DeleteMessage($ids, $user2, $room_resource));
        event(new CardDeleteMessage($room_resource->toResponse(request())->getData()->data, $user2));
        return response()->json([
            'status' => 200,
            'message' => 'message deleted',
        ]); */

        $response = $this->chatService->deleteMessages($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        return response()->json($response);

    }

    public function deleteForMe(DeleteForMeRequest $request)
    {
        /* $user = $request->user();
        foreach ($request->id as $id) {
            $message = ChatMessage::find($id);
            if (!$message) {
                return response()->json([
                    'status' => 404,
                    'message' => 'message not found',
                ], 404);
            }
            $check_room = ChatRoom::find($message->chat_room_id);

            if ($check_room->user_id !== $user->id && $check_room->user_id2 !== $user->id) {
                return response()->json([
                    'status' => 404,
                    'message' => 'message not found',
                ], 404);
            }

            if ($message->user_id == $user->id) {
                $message->user_1_deleted = Carbon::now();
            } else {
                $message->user_2_deleted = Carbon::now();
            }
            $message->update();
        }
        return response()->json([
            'status' => 200,
            'message' => 'message deleted',
        ]); */

        $response = $this->chatService->deleteForUser($request->id, $request->user());

        if ($response['status'] !== 200) {
            return response()->json($response, $response['status']);
        }

        return response()->json($response);
    }
}
