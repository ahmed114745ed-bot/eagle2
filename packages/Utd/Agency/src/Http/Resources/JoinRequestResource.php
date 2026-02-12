<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JoinRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user?->id,
                'uuid' => $this->user?->uuid,
                'name' => $this->user?->name,
                'img' => $this->user?->img ? asset('storage/'.$this->user->img) : null,
            ],
            'agency' => [
                'id' => $this->agency?->id,
                'name' => $this->agency?->name,
                'img' => $this->agency?->img ? asset('storage/'.$this->agency->img) : null,
            ],
            'whatsapp' => $this->whatsapp,
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
            1 => 'Accepted',
            2 => 'Rejected',
            default => 'Unknown',
        };
    }
}
