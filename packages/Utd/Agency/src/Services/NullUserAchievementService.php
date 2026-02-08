<?php

namespace Utd\Agency\Services;

use Utd\Agency\Contracts\UserAchievementServiceInterface;

/**
 * Null Object Pattern for UserAchievementService
 * Used when the actual service is not available
 */
class NullUserAchievementService implements UserAchievementServiceInterface
{
    public function insertCharging($user, $amount)
    {
        return null;
    }

    public function updateAchievement(int $userId, string $achievementType, $value)
    {
        return null;
    }

    public function getUserAchievements(int $userId)
    {
        return [];
    }

    public function hasCompletedAchievement(int $userId, int $achievementId): bool
    {
        return false;
    }
}
