<?php

namespace Utd\Agency\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencySalaryResource extends JsonResource
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
            ],
            'month' => $this->month,
            'year' => $this->year,
            'salary' => $this->sallary,
            'cut_amount' => $this->cut_amount,
            'net_salary' => $this->sallary - $this->cut_amount,
            'is_paid' => (bool) $this->is_paid,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
