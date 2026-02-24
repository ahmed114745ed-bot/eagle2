<?php

namespace Utd\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'chat_with_friends' => ($this->chat_with_friends == 1 ? true : false),
            'chat_with_all' => ($this->chat_with_all == 1 ? true : false),
        ];
    }
}
