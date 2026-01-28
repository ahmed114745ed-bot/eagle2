<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'agency' => [
                'id' => $this->agency?->id,
                'name' => $this->agency?->name,
                'img' => $this->agency?->img ? asset('storage/' . $this->agency->img) : null,
            ],
            'join_date' => $this->join_date?->format('Y-m-d H:i:s'),
            'leave_date' => $this->leave_date?->format('Y-m-d H:i:s'),
            'is_active' => is_null($this->leave_date),
        ];
    }
}
