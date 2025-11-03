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
        $currentUserId = $request->user()->id;
        $user2 = $this->user_id == $currentUserId ? User::find($this->user_id2) : User::find($this->user_id);
    
        \Log::info('Chat Receiver: ', ['receiver_id' => $user2->id, 'receiver_name' => $user2->name , 'chat_room_id' => $this->id]);
    
        $total_undread_message = ChatMessage::where('chat_room_id', $this->id)
            ->where('user_id', '!=',$user2->id)
            ->whereRaw("LOWER(status) != 'seen'")
            ->count();
    
        \Log::info('Total unread messages: ', ['total_undread_message' => $total_undread_message]);
    
        return [
            'user_id'        => $user2->id,
            'name'           => $user2->name,
            'img'            => @$user2->profile->avatar,
            'chat_id'        => $this->id,
            'type'           => $this->type,
            'unread_message' => $total_undread_message,
            'last_message'   => @$this->messages->first() ? new ChatMessageResource($this->messages->first()) : null,
        ];
    }
}
