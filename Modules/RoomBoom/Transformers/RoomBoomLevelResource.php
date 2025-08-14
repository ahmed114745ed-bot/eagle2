<?php

namespace Modules\RoomBoom\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomLevelResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'min_target' => $this->min_target,
            'target' => $this->target,
            'room_booms' => RoomBoomResource::collection($this->whenLoaded('roomBooms')),
            'rewards' => RoomBoomRewardResource::collection($this->whenLoaded('roomBoomRewards')),
        ];
    }
}
