<?php

namespace Modules\Achievement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Achievement\Entities\AchievementLevel;

class UserAchievementLevelsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $description = app()->getLocale() === 'ar' ? $this->ar_description : $this->en_description;
        $title = app()->getLocale() === 'ar' ? ' هذا الإنجاز مأخوذ من المشرف' : 'this achievement is taken from Admin';
        $achievementLevel = AchievementLevel::find($this->achievement_level_id);
        $type = @$achievementLevel?->achievement?->type?->value;
        return [
            'id' => $this->id,
            'image' => $this->valid_image ?? $this->custom_image,
            'description' => $this->custom_image ? ($title ?? '') : ($description ?? ''),
            'type'  => $this->type == "room_target" ? 2 : 1,
        ];
    }
}
