<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ChargeReceivedInfoResource extends JsonResource
{
    public function toArray($request)
    {
        $sender = $this->sender;
        $s_type = 'user';

        $sender_data = [
            'id' => @$sender->id ?? 0,
            'uuid' => @$sender->uuid ?: 0,
            'name' => @$sender->name ?? '',
            'img' => @$sender->img ?? '',
            'type' => $s_type,
        ];

        return [
            'id' => $this->id,
            'sender' => $sender_data,
            'value' => $this->amount,
            'usd' => $this->usd,
            'time' => Carbon::parse($this->created_at)->format('Y-m-d h:i:s A'),
        ];
    }
}
