<?php

namespace App\Http\Resources;

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
        return [
            'id'         => $this->user->id ?? 0,
            'uuid'       => $this->user->uuid ?? '',
            'image'      => $this->user->avatar ?? '',
            'name'       => $this->user->name ?? '',
            'date'       => $this->created_at ?? '',
            'totalUsed'  => (int) ($this->usd ?? 0),
            'coins'      => $this->amount ?? 0,
            
        ];
    }
}

 