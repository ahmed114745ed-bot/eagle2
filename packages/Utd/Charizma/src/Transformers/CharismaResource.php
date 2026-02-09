<?php

namespace Utd\Charizma\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CharismaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'total' => intval($this->total) ?? 0,
            'position' => $this->position ?? 0
        ];
    }
}
