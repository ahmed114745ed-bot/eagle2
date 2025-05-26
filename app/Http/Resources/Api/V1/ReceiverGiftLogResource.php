<?php

namespace App\Http\Resources\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverGiftLogResource extends JsonResource
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
            'id' => @$this->receiver->id ?? 0, // both
            'uuid' => @$this->receiver->uuid ?? '', // both
            'name' => @$this->receiver->name ?: '', // both
            'image' => $this->receiver->profile->avatar ?: '',
            'exp'   => $this->exp ?? '',
            'level'=> Common::level_center_min (@$this->receiver->id), // refactor

        ];

        return $data;
    }
}
