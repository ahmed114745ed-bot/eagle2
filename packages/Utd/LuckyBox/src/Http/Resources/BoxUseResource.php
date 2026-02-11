<?php

namespace Utd\LuckyBox\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BoxUseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'box_id' => $this->box_id,
            'user_id' => $this->user_id,
            'coins' => $this->coins,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'room_uid' => $this->room_uid,
            'room_id' => $this->room_id,
            'users_num' => $this->users_num,
            'used_num' => $this->used_num,
            'used_coins' => $this->used_coins,
            'not_used_num' => $this->not_used_num,
            'unused_coins' => $this->unused_coins,
            'type' => $this->type == 1 ? 'super' : 'normal',
            'label' => $this->label,
            'image' => $this->image,
            'is_closed' => $this->is_closed,
        ];
    }
}
