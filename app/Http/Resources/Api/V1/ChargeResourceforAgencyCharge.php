<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeResourceforAgencyCharge extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sender = $this->sender;
        $receiver = $this->receiver;
        $sender_data   = [
            'id'  => @$sender->id ?: 0, 'uuid' => @$sender->uuid ?: '', 'name' => @$sender->name ?: "",
            'img' => @$sender->img ?: "", 'type' => @$this->charger_type
        ];
        $receiver_data = [
            'id'  => $receiver?->id ?: 0, 'uuid' => @$receiver?->uuid ?: '', 'name' => $receiver?->name ?: "",
            'img' => $receiver?->img ?: "", 'type' => $this->user_type
        ];

        return [
            'id'   => $this->id ?: 0, 'sender' => $sender_data, 'receiver' => $receiver_data, 'value' => (int) $this->amount,
            'time' => ($this->created_at ? $this->created_at->format('Y-m-d h:i:s A') : null)
        ];
    }
}
