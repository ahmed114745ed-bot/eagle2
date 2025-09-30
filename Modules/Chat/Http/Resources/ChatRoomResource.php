<?php

namespace Modules\Chat\Http\Resources;

use App\Helpers\Common;
use Modules\Chat\Entities\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{

    public function toArray(Request $request)
    {

        if ($this->user_id !== $request->user()->id) {
            $user  = User::withTrashed()->find($this->user_id2);
            $user2 = User::withTrashed()->find($this->user_id);
        } else {
            $user  = User::withTrashed()->find($this->user_id);
            $user2 = User::withTrashed()->find($this->user_id2);
        }

        $total_undread_message = ChatMessage::where('chat_room_id', $this->id)->where('user_id', 'not Like', $user->id)->where('status', 'not Like', 'seen')->count();
        $hasColor = Common::hasInPack($user2, 18, true);

        return [
            'user_id'             => @$user2->id,
            'name'                => @$user2->name,
            'img'                 => @$user2->profile->avatar,
            'in_room'             => @$user2->now_room_uid ? true : false,
            'chat_id'             => $this->id,
            'unread_message'      => $total_undread_message,
            'colored_name'        => $hasColor ? common::wareUserVip($user2, 18, 'color') ?? '' : '',
            'last_message'        => @new ChatMessageResource($this->messages[0]),
        ];
    }
}
