<?php

namespace Utd\Achievements\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Utd\Achievements\Entities\Achievement;
use Utd\Achievements\Entities\GiftAchievement;

trait HasGiftAchievement
{
    /**
     * Get the achievement associated with this gift.
     */
    public function achievement(): BelongsTo
    {
        if (! class_exists(Achievement::class)) {
            return $this->belongsTo(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->belongsTo(GiftAchievement::class, 'id', 'gift_id');
    }
}
