<?php

namespace Utd\Agency\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AgencyMonthlyHostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'show_id' => $this->show_id,
            'img' => $this->img,
            'type_user' => $this->type_user,
            'diamonds' => $this->diamonds ?? 0,
            'monthly_diamonds' => $this->monthly_diamonds ?? 0,
            'level' => $this->level,
            'status' => $this->status,
            'is_online' => $this->is_online ?? false,
            'joined_at' => $this->pivot?->created_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
