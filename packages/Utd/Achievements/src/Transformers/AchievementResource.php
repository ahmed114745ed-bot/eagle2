<?php

namespace Utd\Achievements\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AchievementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'valid_image' => $this->valid_image,
            'invalid_image' => $this->invalid_image,
            'target' => $this->target,
            'target_type' => $this->target_type,
        ];
    }
}
