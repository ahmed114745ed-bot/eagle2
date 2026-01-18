<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Dynamic Achievement Trait
 *
 * Checks for package existence before any operation.
 * Returns empty collection if package not installed.
 */
trait DynamicAchievementTrait
{
    /**
     * User Achievement Levels Relation
     */
    public function achievementLevels(): HasMany
    {
        if (class_exists('Utd\\Achievements\\Entities\\UserAchievementLevel')) {
            return $this->hasMany('Utd\\Achievements\\Entities\\UserAchievementLevel', 'user_id');
        }
        // Return empty relation
        return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
    }

    /**
     * Enabled Achievement Levels
     */
    public function enabledAchievementLevels(): HasMany
    {
        if (class_exists('Utd\\Achievements\\Entities\\UserAchievementLevel')) {
            return $this->hasMany('Utd\\Achievements\\Entities\\UserAchievementLevel', 'user_id')
                ->where('is_enable', true);
        }
        return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
    }

    /**
     * Get first N achievements for display
     */
    public function getDisplayAchievements(int $limit = 3): Collection
    {
        if (!class_exists('Utd\\Achievements\\Entities\\UserAchievementLevel')) {
            return collect([]);
        }
        return $this->enabledAchievementLevels()
            ->with('achievementLevel')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if achievement feature is available
     */
    public function hasAchievementFeature(): bool
    {
        return class_exists('Utd\\Achievements\\Entities\\UserAchievementLevel');
    }
}
