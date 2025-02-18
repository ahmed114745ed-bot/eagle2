<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserVip;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id ?? 0,
            'uuid' => $this->uuid ?? 0,
            'image' => $this->profile?->avatar ?? '',
            'country' => @$this->country,
            'is_followed'            => $this->is_followed,
            'is_follow'            => $this->is_follow, // user data  ----
            'is_friend'            => $this->isFriends(),
        ];
    }
}
