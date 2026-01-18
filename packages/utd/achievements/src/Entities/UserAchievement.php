<?php

namespace Utd\Achievements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserAchievement Entity - Completely Standalone
 *
 * Uses config-based model binding instead of hard-coded App\Models\User
 */
class UserAchievement extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('achievements.tables.user_achievements', 'user_achievements');
    }

    /**
     * The achievement
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id');
    }

    /**
     * The user - DYNAMIC MODEL BINDING
     *
     * Instead of: return $this->belongsTo(App\Models\User::class)
     * We use config to determine the model class
     */
    public function user(): BelongsTo
    {
        $userModel = config('achievements.models.user');
        $foreignKey = config('achievements.foreign_keys.user', 'user_id');

        return $this->belongsTo($userModel, $foreignKey);
    }

    /**
     * Gift achievement relation
     */
    public function giftAchievement(): BelongsTo
    {
        return $this->belongsTo(GiftAchievement::class, 'gift_achievement_id');
    }
}
