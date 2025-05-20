<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $data = [
            'id'   => $this->ware->id ?? 0,
            'image' => $this->ware->img2 ?? '',
            'image_type' => $this->ware->image_type ?? 'svga',
            'key' => $this->ware->key ?? '',


        ];
        return $data;
    }
}
