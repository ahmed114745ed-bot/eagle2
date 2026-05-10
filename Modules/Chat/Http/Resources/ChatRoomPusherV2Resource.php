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
        $authUser = $request->user();

        if ($authUser && $this->user_id == $authUser->id) {
            $user2 = User::find($this->user_id);
        } else {
            $user2 = User::find($this->user_id2);
        }

        $total_unread_message = ChatMessage::where('chat_room_id', $this->id)
            ->where('user_id', $authUser->id)
            ->where('status', '!=', 'seen')
            ->count();


        return [
            'id'             => $this->id,
            'user_id'        => $user2?->id,
            'name'           => $user2?->name,
            'img'            => $user2?->profile?->avatar,
            'chat_id'        => $this->id,
            'type'           => $this->type,
            'unread_message' => $total_unread_message,
            'last_message'   => @$this->messages->first() ? new ChatMessageResource($this->messages->first()) : null,
        ];
    }
}
