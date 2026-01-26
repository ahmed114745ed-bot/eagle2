<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyMemberResource extends JsonResource
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
            'type_user' => $this->type_user,
            'is_admin' => $this->agencyAdmins()->exists(),
            'monthly_diamond_received' => $this->monthly_diamond_received_sum ?? 0,
            'country' => $this->country?->name,
            'joined_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
