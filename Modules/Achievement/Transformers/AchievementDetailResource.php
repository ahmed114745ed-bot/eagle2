<?php

namespace Modules\Achievement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Modules\Achievement\Entities\AchievementLevel;
use Modules\Achievement\Entities\UserAchievement;
use Modules\Achievement\Entities\UserAchievementLevel;

class AchievementDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $achievementLevel = $this->achievementLevel;
        return [
            'id' => $this->id,
            'type' => $achievementLevel?->achievement?->type ?? 'no achievement',
            'image' => $achievementLevel?->valid_image ?? $this->custom_image,
            'description' =>    $achievementLevel ? (auth()->user()->lan == "ar" ? $achievementLevel?->ar_description : $achievementLevel?->ar_description) : __('get it by admin'),
        ];
    }
}
