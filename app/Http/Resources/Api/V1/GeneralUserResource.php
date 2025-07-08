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
            'id'   => @$this->id,
            'uuid' => @$this->uuid,
            'name' => @$this->name ?: '',
            'image' => @$this->profile->avatar ?? '',
            'image_color'          => @$this->color_image,
            'id_image'             => @$this->specialId?->ware?->show_img ?? '',
            'level' => Common::level_center(@$this),


        ];
        return $data;
    }
}
