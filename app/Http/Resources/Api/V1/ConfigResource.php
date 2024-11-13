<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
          return [
            'id' => $this->id,
            'name' => __($this->name),
            'value' => $this->value,
            'desc' => $this->desc,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_hidden' => $this->is_hidden,
            'category' => __($this->category),
            'type' => $this->type,
            'sub_type' => $this->sub_type,
        ];
    }
}
