<?php

namespace Utd\Achievements\Null;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Utd\Achievements\Contracts\AchievementServiceInterface;

/**
 * Null Object Pattern Implementation
 *
 * This class provides a "do nothing" implementation of the AchievementServiceInterface.
 * When the achievement package is not installed or not licensed, this class is used
 * instead of the real implementation to prevent errors.
 *
 * Benefits:
 * - No null checks needed in the base code
 * - Graceful degradation when feature is disabled
 * - Clean separation of concerns
 */
class NullAchievementService implements AchievementServiceInterface
{
    public function getUserAchievements(Model $user): Collection
    {
        return collect();
    }

    public function getUserMedals(Model $user): Collection
    {
        return collect();
    }

    public function trackCharging(Model $user, int $totalCoins): void
    {
        // Do nothing - achievements not enabled
    }

    public function trackRoomTarget(Model $user, int $totalCoins): void
    {
        // Do nothing - achievements not enabled
    }

    public function trackGiftTarget(Model $gift, int $total): void
    {
        // Do nothing - achievements not enabled
    }

    public function isEnabled(): bool
    {
        return false;
    }

    public function all(): Collection
    {
        return collect();
    }
}
