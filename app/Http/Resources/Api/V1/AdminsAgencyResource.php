<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminsAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */




    public function toArray($request)
    {

        return [
            'id' => $this->user->id ?? 0,
            'name' => @$this->user->name ?? '', // both
            'uuid' => $this->user->uuid ?? '',
            'image' => @$this->user->profile->avatar ?? '',
            'exp'   => '0' ,
             'image_color'          => @$this->user->color_image,
            'id_image'             => @$this->user->specialId?->ware?->show_img ?? '',
        ];
    }
}
