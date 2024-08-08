<?php

namespace Modules\SwitchAccount\Transformers;

use App\Models\User;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Entities\ChatMessage;
use Modules\SwitchAccount\Entities\UserAccount;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request)
    {
        $userId = \Auth::user()->id;
        $user_acount = UserAccount::query()->where(function ($q) use ($userId) {
            $q->where("parent_user_id", $this->id)->orWhere("child_user_id", $this->id);
        })->first();
        $chats_id = ChatRoom::where('user_id', $this->id)->orWhere('user_id2',$this->id)->pluck('id')->toArray();
        $total_unread_message=  ChatMessage::whereIn('chat_room_id', $chats_id)->where('user_id','not Like',$this->id)->where('status','not Like','seen')->count();
        return [
            'id'            =>  $this->id,
            'image'         =>  $this->profile->avatar,
            'name'          =>  $this->name,
            'uuid'          => $this->uuid,
            'user_type'     => $this->type_user,
            'sender_level'  => $this->total_sender_level ?? 0,
            'received_level'  => $this->total_received_level ?? 0,
            'unread_messages'  => $total_unread_message ?? 0,
            'key'           =>  $user_acount->key,
            'expire'        =>  $user_acount->expire,
            'can_switch'    => ($this->id == $userId ? false : true),
        ];
    }
}
