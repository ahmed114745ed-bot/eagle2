<?php

namespace Utd\Room\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en ?? $this->name,
            'img' => $this->img,
            'parent_id' => $this->parent_id,
            'children' => RoomCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
