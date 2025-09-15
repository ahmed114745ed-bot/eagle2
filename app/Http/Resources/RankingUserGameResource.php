<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingUserGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->id,
            'color_name' => $this->color_name,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'frame' => $this->frame,
            'frame_id' => $this->frame_id,
            'type_user' => $this->type_user,
            'manger_type' => $this->manger_type,
            'vip_level' => '',
            'sender_level' => '',
            'reciver_level' => '',
            'vip_level_img' => '',
            'sender_level_img' => $this?->senderLevel?->img,
            'reciver_level_img' => $this->receiverLevel?->img,
            'country' => [
            
            ],
            'age' => $this->age,
            'achievement_images' => [],
        ];
    }
}

