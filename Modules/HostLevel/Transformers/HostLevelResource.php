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
            'level' => $this->level,
            'name' => $this->name,
            'rewards'=> WeeklyStarGift::collection($this->whenLoaded('rewards')),
        ];
    }
}
