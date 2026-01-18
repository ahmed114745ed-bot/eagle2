<?php
namespace App\Contracts;

interface AchievementContract
{
    /**
     * Get user achievements (first 3 for display)
     */
    public function getUserAchievements(int $userId, int $limit = 3): array;

    /**
     * Get all enabled medals for user
     */
    public function getEnabledMedals(int $userId): array;

    /**
     * Calculate and assign achievement to user
     */
    public function calculateAchievement(int $userId, string $type, float $amount): void;

    /**
     * Check if user has specific achievement
     */
    public function hasAchievement(int $userId, int $achievementId): bool;

    /**
     * Get achievement statistics
     */
    public function getStatistics(int $userId): array;
}
