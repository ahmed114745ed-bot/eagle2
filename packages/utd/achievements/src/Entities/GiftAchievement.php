<?php

namespace Utd\Achievements\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * GiftAchievement Entity
 */
class GiftAchievement extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('achievements.tables.gift_achievements', 'gift_achievements');
    }

    /**
     * The gift - Dynamic binding
     */
    public function gift(): BelongsTo
    {
        $giftModel = config('achievements.models.gift');
        return $this->belongsTo($giftModel, config('achievements.foreign_keys.gift', 'gift_id'));
    }

    /**
     * The user who owns this gift achievement
     */
    public function user(): BelongsTo
    {
        $userModel = config('achievements.models.user');
        return $this->belongsTo($userModel, config('achievements.foreign_keys.user', 'user_id'));
    }

    /**
     * User achievements for this gift
     */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class, 'gift_achievement_id');
    }
}
