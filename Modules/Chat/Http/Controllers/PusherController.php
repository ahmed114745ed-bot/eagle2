<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use  Modules\Chat\Jobs\ReciveChatMessagejob;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Chat\Http\Services\PusherService;

class PusherController extends Controller
{

    public function __construct(public PusherService $pusherService)
    {

    }
    public function edit_user(Request $request) {

        if (getallheaders()['X-Pusher-Key'] != config('broadcasting.connections.pusher.key')) {
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
