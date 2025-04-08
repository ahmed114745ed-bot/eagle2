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
        if ($this->charger_type == 'dash' && $this->user_type == 'dash') {
            $sender_data = [
                'id'  => $this->admin?->id ?: 0,
                'uuid' =>  '',
                'name' => $this->admin?->name ?: "",
                'img' => $this->admin?->avatar ?? "",
                'type' => $this->user_type
            ];
        } else {
            $sender_data   = [
                'id'  => @$sender->id ?: 0,
                'uuid' => @$sender->uuid ?: '',
                'name' => @$sender->name ?: "",
                'img' => @$sender->profile?->avatar ?? "",
                'type' => @$this->charger_type
            ];
        }

        $receiver_data = [
            'id'  => $receiver?->id ?: 0,
            'uuid' => @$receiver?->uuid ?: '',
            'name' => $receiver?->name ?: "",
            'img' => $receiver?->profile?->avatar ?? "",
            'type' => $this->user_type
        ];



        return [
            'id'   => $this->id ?: 0,
            'sender' => $sender_data,
            'receiver' => $receiver_data,
            'value' => (int) $this->amount,
            'time' => ($this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d h:i:s A') : null),
            'coins' =>  (int)$this->amount ?? 0,
            'usd' => $this->usd ?? 0,
        ];
    }
}
