<?php

namespace Utd\Achievements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Achievement Entity - Completely Standalone
 *
 * No dependencies on base project!
 */
class Achievement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'type' => 'string',
        'target_type' => 'string',
    ];

    /**
     * Get table name from config
     */
    public function getTable(): string
    {
        return config('achievements.tables.achievements', 'achievements');
    }

    /**
     * Achievement levels
     */
    public function levels(): HasMany
    {
        return $this->hasMany(AchievementLevel::class, 'achievement_id');
    }

    /**
     * User achievements
     */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * User achievement levels
     */
    public function userAchievementLevels(): HasMany
    {
        return $this->hasMany(UserAchievementLevel::class);
    }

    /**
     * Get users with this achievement - Dynamic model binding
     */
    public function users()
    {
        $userModel = config('achievements.models.user');
        $foreignKey = config('achievements.foreign_keys.user', 'user_id');

        return $this->hasManyThrough(
            $userModel,
            UserAchievement::class,
            'achievement_id',
            'id',
            'id',
            $foreignKey
        );
    }
}
