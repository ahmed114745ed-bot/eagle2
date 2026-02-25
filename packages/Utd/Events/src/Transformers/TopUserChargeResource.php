<?php

namespace Utd\Events\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TopUserChargeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [

            'user_id' => $this->id,
            'uuid' => $this->uuid ?? 0,
            'name' => $this->name ?? '',
            'avatar' => $this->profile->avatar ?? '',
        ];
    }
}
