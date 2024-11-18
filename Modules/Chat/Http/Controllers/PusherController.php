<?php

namespace Modules\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
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

        /* if (getallheaders()['X-Pusher-Key'] != config('broadcasting.connections.pusher.key')) {
            Log::info('Pusehr error');

            abort(403, 'Invalid Pusher webhook request');
        }

//        Log::info('this is response pusher '. json_encode($request->all()));
        $channel = $request->events[0]['channel'];
        $name = $request->events[0]['name'];
        $parts = explode('-', $channel);

        if (count($parts) === 2) {
            $chnnel_name = $parts[0];
            $number = $parts[1];
          if($chnnel_name == 'user')
          {
            $user = User::find($number);
            if($user)
            {
                if($name  =='channel_vacated')
                {
               // Log::info("تم تسجيل اليوز اونلاين");

                    $user->online = 0;
                    $user->current_room_chat  = null ;
                }
                else{
              //  Log::info("تم تسجيل اليوز اوفلاين");

                    $user->online = 1;
                    $chats_id = ChatRoom::where('user_id', $user->id)->orWhere('user_id2', $user->id)->get()->pluck('id')->toArray();;
                    $total_unread =  ChatMessage::whereIn('chat_room_id', $chats_id)->where('user_id','not Like',$user->id)->where('status','sended')->get();
                    dispatch(new ReciveChatMessagejob($total_unread , 'received'));
                }
                $user->save();
            }
          }
        }
        return response()->json(['status' => 'Webhook received']); */

        if (getallheaders()['X-Pusher-Key'] != config('broadcasting.connections.pusher.key')) {
            Log::info('Pusher error');
            abort(403, 'Invalid Pusher webhook request');
        }

        // Extract relevant data from the request
        $channel = $request->events[0]['channel'];
        $eventName = $request->events[0]['name'];

        // Delegate handling user status to the service
        $this->pusherService->handleUserStatusChange($channel, $eventName);

        return response()->json(['status' => 'Webhook received']);
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
