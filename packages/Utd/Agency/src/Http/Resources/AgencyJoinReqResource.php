<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyJoinReqResource extends JsonResource
{
    public function toArray($request)
    {
        $statuses = [
            0 => 'pending',
            1 => 'accepted',
            2 => 'denied',
        ];

        return [
            'agency' => $this->agency,
            'status' => $statuses[$this->status ?: 0],
        ];
    }
}
