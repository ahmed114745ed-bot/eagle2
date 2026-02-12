<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyRequestResource extends JsonResource
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
            'img' => $this->img ? asset('storage/'.$this->img) : null,
            'status' => $this->status,
            'owner' => [
                'id' => $this->owner?->id,
                'uuid' => $this->owner?->uuid,
                'name' => $this->owner?->name,
                'img' => $this->owner?->img ? asset('storage/'.$this->owner->img) : null,
            ],
            'additional_info' => $this->whenLoaded('additionalInfo', fn () => new AdditionalInfoResource($this->additionalInfo)),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
