<?php

namespace App\Http\Resources;

use App\Helpers\UserLevelHelper;
use App\Helpers\UserPackHelper;
use App\Http\Resources\Api\V1\MangerTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingUserGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->id,
            'color_name'        => UserPackHelper::getColorName($this->resource),
            'name' => $this->name,
            'avatar'            => $this->relationLoaded('profile') ? $this->profile?->avatar : null,
            'frame' => $this->frame,
            'frame_id' => $this->frame_id,
            'type_user' => $this->type_user,
            'manger_type'       => $this->relationLoaded('mangerType') ? new MangerTypeResource($this->mangerType) : null,
            'vip_level' => '',
            'sender_level' => '',
            'reciver_level' => '',
            'vip_level_img' => '',
           'sender_level_img'  => UserLevelHelper::getSenderImage($this->resource),
            'reciver_level_img' => UserLevelHelper::getReceiverImage($this->resource),
            'country' => [
            
            ],
            'age'               => $this->relationLoaded('profile') ? ($user->profile?->age ?? null) : null,
            'achievement_images' => [],
        ];
    }
}

