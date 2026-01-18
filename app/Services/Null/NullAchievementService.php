<?php
namespace App\Services\Null;

use App\Contracts\AchievementContract;

class NullAchievementService implements AchievementContract
{
    public function getUserAchievements(int $userId, int $limit = 3): array
    {
        return [];
    }

    public function getEnabledMedals(int $userId): array
    {
        return [];
    }

    public function calculateAchievement(int $userId, string $type, float $amount): void
    {
        // Do nothing - feature not available
    }

    public function hasAchievement(int $userId, int $achievementId): bool
    {
        return false;
    }

    public function getStatistics(int $userId): array
    {
        return [
            'total_achievements' => 0,
            'enabled_medals' => 0,
            'latest_achievement' => null,
        ];
    }
}
