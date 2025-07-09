<?php

namespace App\Http\Resources;

use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DollarChargeAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $receiver=   Common::getReceiverInfo($this);
   
   dd($receiver);

        $sender=   Common::getChargerInfo($this);
        $is_sender = $sender['id'] == Auth::user()->id;
        
        return [
            'id'         => $receiver['id'] ,
            'uuid'       => $receiver['uuid'],
            'image'      => $receiver['image'] ?? '',
            'name'       => $receiver['name'] ?? '',
            'date'       => $this->created_at ?? '',
            'totalUsed'  => (int) ($this->usd ?? 0),
            'coins'      => $this->amount ?? 0,
            'is_sender'      => $is_sender ?? 0,


        ];
    }
}
