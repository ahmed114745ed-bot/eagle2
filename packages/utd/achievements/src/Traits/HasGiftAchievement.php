<?php

namespace Utd\Achievements\Traits;

use Utd\Achievements\Entities\GiftAchievement;

trait HasGiftAchievement
{
    /**
     * Get the achievement associated with this gift.
     */
    public function achievement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GiftAchievement::class, 'id', 'gift_id');
    }
}
