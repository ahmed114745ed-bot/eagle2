<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DollarChargeAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->agency->id ,
            'uuid'       => '',
            'image'      => $this->agency->img ?? '',
            'name'       => $this->agency->name ?? '',
            'date'       => $this->created_at ?? '',
            'totalUsed'  => (int) ($this->usd ?? 0),
            'coins'      => $this->amount ?? 0,

        ];
    }
}
