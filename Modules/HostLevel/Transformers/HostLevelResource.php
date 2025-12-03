<?php

namespace Modules\HostLevel\Transformers;


use Modules\Events\Transformers\WeeklyStarGift;
use Illuminate\Http\Resources\Json\JsonResource;

class HostLevelResource extends JsonResource
{

    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'diamond' => $this->diamonds,
            'level' => $this->level,
            'name' => $this->name,
            'img' => $this->img,
            'rewards'=> WeeklyStarGift::collection($this->whenLoaded('rewards')),
        ];
    }
}
