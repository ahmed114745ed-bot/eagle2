<?php

namespace Utd\Achievements\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Null Object Trait for Achievement Relations
 * 
 * This trait provides empty implementations of achievement relations.
 * Use this in your User model when the achievement package is not installed
 * or you want to conditionally disable achievements.
 * 
 * Benefits:
 * - Code won't break if calling $user->medals()
 * - Returns empty collections instead of errors
 * - Smooth transition between enabled/disabled states
 */
trait NullHasAchievements
{
    /**
     * Return empty query - no medals table exists
     */
    public function medals(): Collection
    {
        return collect();
    }

    /**
     * Return empty collection
     */
    public function enabledMedals(): Collection
    {
        return collect();
    }

    /**
     * Return empty collection
     */
    public function pickedMedals(): Collection
    {
        return collect();
    }
}
