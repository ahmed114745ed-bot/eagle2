<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class SenderGiftLogResource extends JsonResource
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
            'id' => @$this->sender->id ?? 0, // both
            'uuid' => @$this->sender->uuid ?? '', // both
            'name' => @$this->sender->name ?: '', // both
            'image' => $this->sender->profile->avatar ?? '',
            'image_color'          => @$this->sender->color_image,
            'id_image'             => @$this->sender->specialId?->ware?->show_img ?? '',
            'exp'   => $this->exp ?? '',
            'level' => Common::level_center_min(@$this->sender->id),

        ];

        return $data;
    }
}
