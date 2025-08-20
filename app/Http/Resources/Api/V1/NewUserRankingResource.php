<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class NewUserRankingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id'          => $this->user_id,
            'uuid'             => $this->uuid ?? '',
            'exp'              => $this->exp,
            'exp_int'          => $this->exp_int,
            'remaining'        => $this->remaining,
            'remaining_int'    => $this->remaining_int,
            'name'             => $this->name,
            'avatar'           => $this->profile->avatar ?? '',
            'frame'            => $this->frame,
            'frame_id'         => $this->frame_id,
            'vip_level'        => $this->vip_level,
            'sender_level'     => $this->sender_level,
            'reciver_level'    => $this->reciver_level,
            'vip_level_img'    => $this->vip_level_img,
            'sender_level_img' => $this->sender_level_img,
            'reciver_level_img'=> $this->reciver_level_img,
            'country'          => $this->country,
            'age'              => $this->age,
            'type_user'        => $this->type_user,
            'manger_type'      => $this->manger_type,
            'achievement_images' => $this->achievement_images ?? [],
            'color_name'       => $this->color_name,
            'room'             => $this->room,
        ];
    }
}
