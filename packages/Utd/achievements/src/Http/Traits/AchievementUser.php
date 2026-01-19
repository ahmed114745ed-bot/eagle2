<?php

namespace Utd\Achievements\Http\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Utd\Achievements\Entities\UserAchievementLevel;

/**
 * Trait for User model to access achievements/medals
 */
trait AchievementUser
{
    public function medals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id');
    }

    public function enabledMedals(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'user_id')->where('is_enable', true);
    }
}
