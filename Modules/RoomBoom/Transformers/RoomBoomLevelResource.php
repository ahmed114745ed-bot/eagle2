<?php

namespace Modules\RoomBoom\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomBoomLevelResource extends JsonResource
{
    public function toArray($request)
    {
        $data =  [
            'id' => $this->id,
            'level' => $this->level,
            'min_target' => $this->whenHas('min_target'),
            'target' => $this->whenHas('target'),
            'video' => $this->video,
            'room_booms' => RoomBoomResource::make($this->whenLoaded('roomBooms')),
            'rewards' => RoomBoomRewardResource::collection($this->whenLoaded('roomBoomRewards')),
        ];

        return $data;
    }
}
