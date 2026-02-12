<?php

namespace Utd\Room\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BackgroundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'img' => $this->img,
            'enable' => (bool) $this->enable,
            'created_at' => $this->created_at,
        ];
    }
}
