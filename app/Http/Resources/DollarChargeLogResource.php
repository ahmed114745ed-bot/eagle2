<?php

namespace App\Http\Resources;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DollarChargeLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $receiver=   Common::getReceiverInfo($this);
      
        return [
            'id'        => $receiver['id'] ?? 0,
            'uuid'      => $receiver['uuid'] ?? '',
            'image'     => $receiver['image'] ?? '',
            'name'      => $receiver['name'] ?? '',
            'date'       => $this->created_at ?? '',
            'totalUsed'  => (int) ($this->usd ?? 0),
            'coins'      => $this->amount ?? 0,
            
        ];
    }
}

 