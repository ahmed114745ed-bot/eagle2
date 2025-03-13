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
        Log::info('Pusher User');
        Log::info(json_encode($request->all()));
        
        if (getallheaders()['X-Pusher-Key'] != config('broadcasting.connections.pusher.key')) {
            // Log::info('Pusher error');
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
