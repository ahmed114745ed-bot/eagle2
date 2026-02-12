<?php

namespace Utd\Agency\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class HostDailyReportResource extends JsonResource
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
            'user_id' => $this->user_id,
            'agency_id' => $this->agency_id,
            'date' => $this->date,
            'diamonds' => $this->diamonds ?? 0,
            'coins' => $this->coins ?? 0,
            'hours' => $this->hours ?? 0,
            'target' => $this->target ?? 0,
            'achievement_rate' => $this->achievement_rate ?? 0,

            // Relations
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'uuid' => $this->user->uuid,
                    'avatar' => $this->user->avatar,
                ];
            }),
            'agency' => $this->whenLoaded('agency'),
        ];
    }
}
