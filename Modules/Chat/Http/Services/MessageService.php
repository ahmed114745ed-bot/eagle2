<?php

namespace Modules\Chat\Http\Services;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\ChatRoom;
use App\Models\User;
use App\Helpers\Common;
use Illuminate\Support\Facades\Log;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom as EntitiesChatRoom;
use Modules\Chat\Http\Repositories\MessageAlbumRepository;
use Modules\Chat\Http\Repositories\MessageRepository;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\Chat\Traits\FfmpegTrait;
use Modules\Public\Events\UnreadCounterIndividual;

class MessageService
{
    use FfmpegTrait;

    protected $messageAlbumRepository;

    public function __construct(MessageAlbumRepository $messageAlbumRepository, public MessageRepository $messageRepo)
    {
        $this->messageAlbumRepository = $messageAlbumRepository;
    }


    public function handleFileUpload(Request $request, $chatRoom, $message, $user)
    {
        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'mp4', 'mp3', 'wav', 'pdf'];
            $count = count($files);

            if ($count == 1) {
                $this->processSingleFile($files[0], $validExtensions, $chatRoom, $message, $user);
            } else {
                $this->processMultipleFiles($files, $validExtensions, $chatRoom, $message, $user);
            }
        }
    }

    private function processSingleFile($file, $validExtensions, $chatRoom, $message, $user)
    {
        $extension = $file->getClientOriginalExtension();
        Log::info('extension : '. $extension);
        if (!$this->isValidExtension($extension, $validExtensions)) {
            return response()->json(['status' => 404, 'message' => "Invalid file type"], 404);
        }

        if (in_array($extension, ['jpeg', 'jpg', 'png'])) {
            $this->processImageFile($file, $chatRoom, $message, $user);
        } elseif ($extension == 'gif') {
            $this->processGifFile($file, $chatRoom, $message, $user);
        } elseif ($extension == 'mp4' || is_string($file)) {
            $this->processVideoFile($file, $chatRoom, $message, $user);
        } elseif (in_array($extension, ['mp3', 'wav', 'm4a', 'aac'])) {
            $this->processAudioFile($file, $chatRoom, $message, $user);
        } elseif ($extension == 'pdf') {
            $this->processPdfFile($file, $chatRoom, $message, $user);
        }
    }

    private function processMultipleFiles($files, $validExtensions, $chatRoom, $message, $user)
    {
        $message->type = 'album';
        $message->update();

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            if ($this->isValidExtension($extension, $validExtensions)) {
                $this->processSingleFile($file, $validExtensions, $chatRoom, $message, $user);
            }
        }
    }

    private function isValidExtension($extension, $validExtensions)
    {
        return in_array($extension, $validExtensions);
    }

    private function processImageFile($file, $chatRoom, $message, $user)
    {
        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
        $this->messageAlbumRepository->createAlbum($chatRoom, $message, $user, $file, $file_name, 'img');

        $message->type = 'img';
        $message->update();
    }

    private function processGifFile($file, $chatRoom, $message, $user)
    {
        $album = $this->messageAlbumRepository->createAlbum($chatRoom, $message, $user, $file, $file->getClientOriginalName(), 'gif');

        $message->type = 'gif';
        $message->message = null;
        $message->update();
    }

    private function processVideoFile($file, $chatRoom, $message, $user)
    {
        if (!is_string($file)) {
            $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
            $name = pathinfo($file_name, PATHINFO_FILENAME);

            $album = $this->messageAlbumRepository->createAlbum($chatRoom, $message, $user, $file, $file_name, 'video');
            $videoPath = $file_name;
            $thumbnailPath = 'Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id . '/' . $name . '.jpg';

            try {
                $this->extract_frame($videoPath, $thumbnailPath);
            } catch (\Throwable $e) {
                return $e->getMessage();
            }
        } else {
            $thumbnailPath = $file;
        }


        $album->frame = $thumbnailPath;
        $album->save();

        $message->type = 'video';
        $message->message = null;
        $message->update();
    }

    private function processAudioFile($file, $chatRoom, $message, $user)
    {
        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
        $this->messageAlbumRepository->createAlbum($chatRoom, $message, $user, $file, $file_name, 'voice');

        $message->type = 'voice';
        $message->message = null;
        $message->update();
    }

    private function processPdfFile($file, $chatRoom, $message, $user)
    {
        $file_name = Common::upload('Chat_' . env('APP_ENV') . '/chat_' . $chatRoom->id, $file);
        $this->messageAlbumRepository->createAlbum($chatRoom, $message, $user, $file, $file_name, 'file');

        $message->type = 'file';
        $message->message = null;
        $message->update();
    }

    public function handleMessage($request, $message, $user, $user2, $chatRoom)
    {
        // Update message status based on user conditions
        $this->updateMessageStatus($message, $user2, $chatRoom);

        // Send notification if user is not logged out
        $this->sendNotification($user2, $message);

        // Handle message reply
        if ($request->message_id) {
            $this->messageRepo->createMessageReplay($message->id, $request->message_id);
        }

        // Return the message and chat room resources
        return [
            'message_resource' => new ChatMessageResource($this->messageRepo->findMessageById($message->id)),
            'room_resource' => new ChatRoomResourcePusher($chatRoom)
        ];
    }

    private function updateMessageStatus(ChatMessage $message, User $user2, EntitiesChatRoom $chatRoom)
    {
        if ($user2->online == 1) {
            $condition = ($user2->current_room_chat == $chatRoom->id);
            $status = $condition ? 'seen' : 'received';
            $this->messageRepo->updateMessageStatus($message, $status);
            if (!$condition) {
                event(new UnreadCounterIndividual('message', $user2, 1));
            }
        }
    }

    private function sendNotification(User $user2, ChatMessage $message)
    {
        if ($user2->is_logout != 1) {
            $notificationId = $this->messageRepo->getUserNotificationId($user2->id);
            $tokens_notfacion = [$notificationId];
            $title = $message->user->name;
            $body = $message->message;
            $type = $message->type ?? 'text';

            Common::send_firebase_notification($tokens_notfacion, $title, $body, messageType: $type, user: $message->user);
        }
    }
}
