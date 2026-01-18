<?php

namespace Utd\Achievements\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Utd\Achievements\Contracts\HasAchievementsInterface;
use Utd\Achievements\Entities\UserAchievementLevel;

/**
 * Trait to add achievement relations to User model
 *
 * Usage in User Model:
 *
 * use Utd\Achievements\Traits\HasAchievements;
 *
 * class User extends Authenticatable implements HasAchievementsInterface
 * {
 *     use HasAchievements;
 * }
 */
trait HasAchievements
{
    /**
     * Get all medals for this user
     */
    public function medals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id');
    }

    /**
     * Get enabled medals only
     */
    public function enabledMedals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id')
            ->where('is_enable', true);
    }

    /**
     * Get picked medals (shown on profile)
     */
    public function pickedMedals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id')
            ->where('is_enable', true)
            ->where('picked', true);
    }
}
