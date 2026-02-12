<?php

namespace Utd\Agency\Contracts;

interface UserAchievementServiceInterface
{
    /**
     * Insert charging achievement for user
     *
     * @param  mixed  $user
     * @param  float|int  $amount
     * @return mixed
     */
    public function insertCharging($user, $amount);

    /**
     * Update user achievement progress
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function updateAchievement(int $userId, string $achievementType, $value);

    /**
     * Get user achievements
     *
     * @return mixed
     */
    public function getUserAchievements(int $userId);

    /**
     * Check if user has completed achievement
     */
    public function hasCompletedAchievement(int $userId, int $achievementId): bool;
}
