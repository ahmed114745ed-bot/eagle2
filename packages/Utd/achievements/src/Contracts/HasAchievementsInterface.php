<?php

namespace Utd\Achievements\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Interface for models that can have achievements
 *
 * This contract defines the relationship methods that a User model
 * should have when achievements are enabled.
 */
interface HasAchievementsInterface
{
    /**
     * Get all medals for this user
     */
    public function medals(): HasMany;

    /**
     * Get enabled medals for this user
     */
    public function enabledMedals(): HasMany;
}
