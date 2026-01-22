<?php

namespace App\Services\Null;

use App\Contracts\AchievementLevelContract;
use Illuminate\Database\Eloquent\Collection;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Entities\UserAchievement;
use Utd\Achievements\Enums\TargetType;

class NullAchievementLevelService implements AchievementLevelContract
{
    public function assignAchievementToUser(?UserAchievement $userAchievement): void
    {
        // No-op
    }

    public function approveAchievement(?UserAchievement $userAchievement, ?array $notificationIds = null): array
    {
        return [];
    }

    public function getAchievement(UserAchievement $userAchievement): ?TargetType
    {
        return null;
    }

    public function getLevelsIds($achievementLevels): array
    {
        return [];
    }

    public function getDefaultLevelsIds($achievementLevels): array
    {
        return [];
    }

    public function getUserAchievementLevels(int $userId, array $levelIds): array|Collection
    {
        return collect();
    }

    public function isGreater($achievementLevelId, array $levelIds, int $targetId): bool
    {
        return false;
    }

    public function assignAchievement(
        int $userId,
        AchievementLevel $achievementLevel,
        array $levelIds,
        array $defaultIds,
        $giftId = null
    ): void {
        // No-op
    }

    public function sendAchievementNotifications(?array $notificationIds): void
    {
        // No-op
    }

    public function setUserAchievementLevel(bool $isTest = false): void
    {
        // No-op
    }

    public function assignAchievementLevelToUserByAdmin(int $userId, AchievementLevel $achievementLevel): bool
    {
        return false;
    }
}
