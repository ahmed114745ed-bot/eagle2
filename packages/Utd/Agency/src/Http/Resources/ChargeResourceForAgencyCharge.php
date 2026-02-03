<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Utd\Agency\Entities\ShippingAgency;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChargeResourceForAgencyCharge extends JsonResource
{
    public function toArray($request)
    {
        $sender = AgencyHelper::getChargerInfo($this);
        $is_sender = ShippingAgency::where('id', $sender['id'])
            ->where('app_owner_id', Auth::user()->id)
            ->exists();

        return [
            'id'   => $this->id ?: 0,
            'sender' => $sender,
            'receiver' =>  AgencyHelper::getReceiverInfo($this),
            'value' => (int) $this->amount,
            'time' => ($this->created_at ? Carbon::parse($this->created_at)->format('Y-m-d h:i:s A') : null),
            'coins' =>  (int)$this->amount ?? 0,
            'usd' => $this->usd ?? 0,
            'is_sender' => $is_sender ?? 0,
        ];
    }
}
