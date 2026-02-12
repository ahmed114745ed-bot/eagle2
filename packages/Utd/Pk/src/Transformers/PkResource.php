<?php

namespace Utd\Pk\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PkResource extends JsonResource
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
            'room_id' => $this->room_id,
            't1_score' => $this->t1_score,
            't2_score' => $this->t2_score,
            't1_per' => $this->t1_per,
            't2_per' => $this->t2_per,
            'team_1_boss' => $this->whenLoaded('team1Boss', fn () => [
                'id' => $this->team1Boss->id,
                'name' => $this->team1Boss->name ?? '',
                'avatar' => $this->team1Boss->profile?->avatar ?? '',
            ]),
            'team_2_boss' => $this->whenLoaded('team2Boss', fn () => [
                'id' => $this->team2Boss->id,
                'name' => $this->team2Boss->name ?? '',
                'avatar' => $this->team2Boss->profile?->avatar ?? '',
            ]),
            'status' => $this->status,
            'end_at' => $this->end_at,
            'created_at' => $this->created_at,
        ];
    }
}
