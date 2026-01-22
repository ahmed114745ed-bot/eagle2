<?php

namespace Modules\Achievement\Http\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Achievements\Entities\GiftAchievement;

trait AchievementGift
{

    public function achievement(): BelongsTo
    {
        if (!class_exists(GiftAchievement::class)) {
            return $this->belongsTo(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->belongsTo(GiftAchievement::class, 'id', 'gift_id');
    }
}
