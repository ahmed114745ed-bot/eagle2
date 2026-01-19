<?php

namespace Utd\Achievements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserAchievementLevel Entity
 */
class UserAchievementLevel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_enable' => 'boolean',
        'picked' => 'boolean',
    ];

    public function getTable(): string
    {
        return config('achievements.tables.user_achievement_levels', 'user_achievement_levels');
    }

    /**
     * The user - Dynamic binding
     */
    public function user(): BelongsTo
    {
        $userModel = config('achievements.models.user');
        return $this->belongsTo($userModel, config('achievements.foreign_keys.user', 'user_id'));
    }

    /**
     * The achievement level
     */
    public function achievementLevel(): BelongsTo
    {
        return $this->belongsTo(AchievementLevel::class, 'achievement_level_id');
    }

    /**
     * The achievement (through level)
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }
}
