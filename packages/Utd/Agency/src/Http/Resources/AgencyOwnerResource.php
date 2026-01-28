<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'img' => $this->img ? asset('storage/' . $this->img) : null,
            'country' => $this->country?->name,
            'phone' => $this->phone,
        ];
    }
}
