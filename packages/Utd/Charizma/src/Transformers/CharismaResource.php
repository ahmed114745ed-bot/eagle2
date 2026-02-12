<?php

namespace Utd\Charizma\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CharismaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'total' => (int) ($this->total) ?? 0,
            'position' => $this->position ?? 0,
        ];
    }
}
