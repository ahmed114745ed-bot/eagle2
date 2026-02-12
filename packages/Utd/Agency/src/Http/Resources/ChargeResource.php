<?php

namespace Utd\Agency\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Utd\Agency\Facades\AgencyHelper;

class ChargeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sender' => AgencyHelper::getChargerInfo($this),
            'receiver' => AgencyHelper::getReceiverInfo($this),
            'value' => $this->amount,
            'usd' => $this->usd,
            'time' => Carbon::parse($this->created_at)->format('Y-m-d h:i:s A'),
        ];
    }
}
