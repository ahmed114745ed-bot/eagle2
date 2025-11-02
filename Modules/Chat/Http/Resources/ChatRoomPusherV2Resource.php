<?php

namespace Modules\Chat\Http\Resources;

use Modules\Chat\Entities\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomPusherV2Resource extends JsonResource
{

    public function toArray(Request $request)
    {
        if($this->user_id == $request->user()->id)
        {
            $user2 = User::find($this->user_id);  
        }
        else{
            $user2 = User::find($this->user_id2); 
        }
        \Log::info('Chat Receiver: ', ['receiver_id' => $user2->id, 'receiver_name' => $user2->name , 'id' => $this->id]);


        $total_undread_message = ChatMessage::where('chat_room_id',$this->id)->where('user_id',$user2->id)->where('status','not Like','seen')->count();
        \Log::info(' $total_undread_message: ', ['total_undread_message' =>  $total_undread_message]);

        return [
            'user_id'             => $user2->id,
            'name'                => $user2->name,
            'img'                 => @$user2->profile->avatar,
            'chat_id'             => $this->id,
            'type'                => $this->type,
            'unread_message'      => $total_undread_message,
            'last_message'        => @ new ChatMessageResource( $this->messages[0]),
        ];
    }
}
