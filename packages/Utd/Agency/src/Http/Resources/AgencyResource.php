<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notice' => $this->notice,
            'phone' => $this->phone,
            'phone_code' => $this->phone_code,
            'img' => $this->img ? asset('storage/' . $this->img) : null,
            'status' => $this->status,
            'type' => $this->type,
            'is_frozen' => $this->is_frozen,
            'owner' => $this->whenLoaded('owner', fn() => new AgencyOwnerResource($this->owner)),
            'members_count' => $this->mempers_count ?? $this->mempers()->count(),
            'admins_count' => $this->admins()->count(),
            'salary' => $this->when($this->salary, $this->salary),
            'target' => $this->when($this->target, $this->target),
            'country' => $this->whenLoaded('country', fn() => [
                'id' => $this->country->id,
                'name' => $this->country->name,
            ]),
            'additional_info' => $this->whenLoaded('additionalInfo', fn() => new AdditionalInfoResource($this->additionalInfo)),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
