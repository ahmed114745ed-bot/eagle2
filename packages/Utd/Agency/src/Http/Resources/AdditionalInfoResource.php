<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'agency_id' => $this->agency_id,
            'face_image' => $this->face_image ? asset('storage/'.$this->face_image) : null,
            'back_image' => $this->back_image ? asset('storage/'.$this->back_image) : null,
            'email' => $this->email,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status text
     */
    protected function getStatusText(): string
    {
        return match ($this->status) {
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
            default => 'Unknown',
        };
    }
}
