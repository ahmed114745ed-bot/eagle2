<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppearChargerAgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'image' => $this->profile?->avatar,
            'agency_coins' => $this->agency->coins,
            'agency_name' => $this->agency->name,
            'agency_image' => $this->agency->img,
            'name' => $this->name,
            'phone' => $this->phone,
            'agency_id' => $this->agency_id,
            'status' => $this->appear_charger_agency,
        ];
    }
}
