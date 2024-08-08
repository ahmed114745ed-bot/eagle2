<?php
namespace Modules\Chat\Traits;

use Modules\Chat\Entities\ChatRoom;
trait ChatUserTrait {
    public function chats()
    {
        return $this->belongsToMany(ChatRoom::class,'pin_to_tops');
    }

}

