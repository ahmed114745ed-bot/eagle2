<?php

namespace Utd\Achievements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AchievementLevel Entity
 */
class AchievementLevel extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('achievements.tables.achievement_levels', 'achievement_levels');
    }

    /**
     * Parent achievement
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    /**
     * Users at this level
     */
    public function userAchievementLevels(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class, 'achievement_level_id');
    }
}
