<?php

namespace Utd\Achievements\Http\Traits;

use Utd\Achievements\Entities\GiftAchievement;

/**
 * Trait for Gift model to access its achievement
 */
trait AchievementGift
{
    public function achievement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GiftAchievement::class, 'id', 'gift_id');
    }
}
