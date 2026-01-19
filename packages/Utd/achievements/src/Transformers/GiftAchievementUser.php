<?php

namespace Utd\Achievements\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftAchievementUser extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'achievement_name' => $this->Achievement?->type ?? '',
            'gift_name' => $this->gift?->name ?? '',
            'user_name' => $this->user?->name ?? '',
        ];
    }
}
