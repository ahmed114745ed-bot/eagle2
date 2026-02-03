<?php

namespace Utd\Agency\Http\Resources;

use Utd\Agency\Facades\AgencyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DollarChargeLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $receiver =   AgencyHelper::getReceiverInfo($this);
        $sender =   AgencyHelper::getChargerInfo($this);
        $is_sender = $sender['id'] == Auth::user()->id;
        $hasColor = AgencyHelper::hasInPack($receiver['id'], 18, true);

        return [
            'id'        => $receiver['id'] ?? 0,
            'uuid'      => $receiver['uuid'] ?? '',
            'image'     => $receiver['image'] ?? '',
            'name'      => $receiver['name'] ?? '',
            'date'       => $this->created_at ?? '',
            'totalUsed'  => (int) ($this->usd ?? 0),
            'coins'      => $this->amount ?? 0,
            'is_sender'      => $is_sender ?? 0,
            'colored_name' => $hasColor ? AgencyHelper::wareUserVip($receiver['id'], 18, 'color') ?? '' : '',
        ];
    }
}
