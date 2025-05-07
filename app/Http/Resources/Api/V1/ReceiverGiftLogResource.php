<?php

namespace App\Http\Resources\Api\V1;

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
        ];

        return $data;
    }
}
