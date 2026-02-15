<?php

namespace Utd\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Utd\Chat\Entities\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Utd\Chat\Http\Services\PusherService;
use Utd\Chat\Http\Services\ChatRoomService;

class PusherController extends Controller
{

    public function __construct(public PusherService $pusherService, public ChatRoomService $chatRoomService)
    {

    }
    public function edit_user(Request $request) {

        $pusherKey = $request->header('X-Pusher-Key');
        if ($pusherKey != config('broadcasting.connections.pusher.key')) {
            abort(403, 'Invalid Pusher webhook request');
        }

        // Extract relevant data from the request
        foreach ($request->events as $event) {
            $channel = $event['channel'];
            $eventName = $event['name'];

            // Delegate handling user status to the service
            $this->pusherService->handleUserStatusChange($channel, $eventName);
        }

        return response()->json(['status' => 'Webhook received']);
    }

    public function chatRoomListener(Request $request)
    {
        $payload = $request->all();
        // \Log::channel('chat_room')->info('Chat Room Listener Triggered', [
        //     'payload'     => $request->all(),
        //     'user_id'     => optional($request->user())->id,
        // ]);
        $timeMs = $payload['time_ms'] ?? null;
        $eventTime = $timeMs ? Carbon::createFromTimestampMs($timeMs) : now();

        $events = $request->input('events', []);

        foreach ($events as $event) {
            $eventName = $event['name'];
            $channel = $event['channel'] ?? null;

            if ($channel && str_starts_with($channel, 'presence-chat.room.')) {
                $roomId = str_replace('presence-chat.room.', '', $channel);
                switch ($eventName) {
                    case 'member_removed':
                        $user = User::find($event['user_id']);
                        if ($user) {
                            $user->current_room_chat = null;
                            $user->save();

                            $eventTimeFormatted = $eventTime->toDateTimeString();

                            ChatMessage::where('chat_room_id', $roomId)
                                ->where('user_id', $user->id)
                                ->where('created_at', '>', $eventTimeFormatted)
                                ->update(['status' => 'received']);

//                            $checkRoom = $this->chatRoomService->getCreateChatRoomId($roomId);
//                            if ($checkRoom) {
//                                $user2 = $this->chatRoomService->getUserInChatRoom($checkRoom, $user);
//                                if ($user2) {
//                                    try {
//                                        $roomResourceData = [
//                                            'user_id'        => $user2?->id ?? ($user2?->id ?? null),
//                                            'name'           => $user2?->name ?? null,
//                                            'img'            => @$user2?->profile->avatar,
//                                            'chat_id'        => $checkRoom->id,
//                                            'type'           => $checkRoom->type,
//                                            'unread_message' => ChatMessage::where('chat_room_id', $checkRoom->id)
//                                                ->where('user_id', $user->id)
//                                                ->where('status', '!=', 'seen')
//                                                ->count(),
//                                            'last_message'   => @$checkRoom->messages->first() ? new ChatMessageResource($checkRoom->messages->first()) : null,
//                                        ];
//
//                                        event(new OpenChat($roomResourceData, $user2 ?? $user, $checkRoom));
//                                    } catch (\Throwable $e) {
//                                        Log::warning('handleChatOpenEvent failed in Pusher webhook: ' . $e->getMessage());
//                                    }
//
//                                    try {
//                                        $roomPayload = [
//                                            'id'             => $checkRoom->id,
//                                            'user_id'        => $user2->id,
//                                            'name'           => $user2->name,
//                                            'img'            => @$user2->profile->avatar,
//                                            'chat_id'        => $checkRoom->id,
//                                            'type'           => $checkRoom->type,
//                                            'unread_message' => ChatMessage::where('chat_room_id', $checkRoom->id)
//                                                ->where('user_id', $user->id)
//                                                ->where('status', '!=', 'seen')
//                                                ->count(),
//                                            'last_message'   => @$checkRoom->messages->first() ? new ChatMessageResource($checkRoom->messages->first()) : null,
//                                        ];
//                                        event(new Chat($roomPayload, $user));
//                                    } catch (\Throwable $e) {
//                                        Log::warning('Sending Chat event failed in Pusher webhook: ' . $e->getMessage());
//                                    }
//                                }
//                            }
                        }
                        break;

                    case 'member_added':
                        break;

                    case 'channel_occupied':
                        break;

                    case 'channel_vacated':
                        //empty
//                        User::where('current_room_chat', $roomId)->update(['current_room_chat' => null]);

                        $eventTimeFormatted = $eventTime->toDateTimeString();

                        $users = User::where('current_room_chat', $roomId)->get();

                        foreach ($users as $user) {
                            $user->update(['current_room_chat' => null]);

                            ChatMessage::where('chat_room_id', $roomId)
                                ->where('user_id', $user->id)
                                ->where('created_at', '>', $eventTimeFormatted)
                                ->update(['status' => 'received']);

//                            $checkRoom = $this->chatRoomService->getCreateChatRoomId($roomId);
//                            if ($checkRoom) {
//                                $user2 = $this->chatRoomService->getUserInChatRoom($checkRoom, $user);
//                                if ($user2) {
//                                    try {
//                                        $roomResourceData = [
//                                            'user_id'        => $user2?->id ?? ($user2?->id ?? null),
//                                            'name'           => $user2?->name ?? null,
//                                            'img'            => @$user2?->profile->avatar,
//                                            'chat_id'        => $checkRoom->id,
//                                            'type'           => $checkRoom->type,
//                                            'unread_message' => ChatMessage::where('chat_room_id', $checkRoom->id)
//                                                ->where('user_id', $user->id)
//                                                ->where('status', '!=', 'seen')
//                                                ->count(),
//                                            'last_message'   => @$checkRoom->messages->first() ? new ChatMessageResource($checkRoom->messages->first()) : null,
//                                        ];
//
//                                        event(new OpenChat($roomResourceData, $user2 ?? $user, $checkRoom));
//                                    } catch (\Throwable $e) {
//                                        Log::warning('handleChatOpenEvent failed in Pusher webhook: ' . $e->getMessage());
//                                    }
//
//                                    try {
//                                        $roomPayload = [
//                                            'id'             => $checkRoom->id,
//                                            'user_id'        => $user2->id,
//                                            'name'           => $user2->name,
//                                            'img'            => @$user2->profile->avatar,
//                                            'chat_id'        => $checkRoom->id,
//                                            'type'           => $checkRoom->type,
//                                            'unread_message' => ChatMessage::where('chat_room_id', $checkRoom->id)
//                                                ->where('user_id', $user->id)
//                                                ->where('status', '!=', 'seen')
//                                                ->count(),
//                                            'last_message'   => @$checkRoom->messages->first() ? new ChatMessageResource($checkRoom->messages->first()) : null,
//                                        ];
//                                        event(new Chat($roomPayload, $user));
//                                    } catch (\Throwable $e) {
//                                        Log::warning('Sending Chat event failed in Pusher webhook: ' . $e->getMessage());
//                                    }
//                                }
//                            }
                        }
                        break;
                }
            }
        }

        return response('OK', 200);
    }

    public function user_status($id) {
        $user = User::find($id);
        if($user != null)
        {
            return [
                'online' =>$user->online
            ] ;
        }else{
            return 'user not found';
        }
    }
}
