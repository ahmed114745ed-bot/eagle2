<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class interestsUsergotResorse extends JsonResource
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
            'id' => $this->interests2->id??'',// Include related interests resource
            'name' => $this->interests2->name??'',// Include related interests resource
            'img' => $this->interests2->img??'',// Include related interests resource
        ];
     }
}
