<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Utd\Achievements\Entities\AchievementLevel;
use Utd\Achievements\Entities\UserAchievement;
use Utd\Achievements\Enums\TargetType;

interface AchievementLevelContract
{
    public function assignAchievementToUser(?UserAchievement $userAchievement): void;

    public function approveAchievement(?UserAchievement $userAchievement, ?array $notificationIds = null): array;

    public function getAchievement(UserAchievement $userAchievement): ?TargetType;

    public function getLevelsIds($achievementLevels): array;

    public function getDefaultLevelsIds($achievementLevels): array;

    public function getUserAchievementLevels(int $userId, array $levelIds): array|Collection;

    public function isGreater($achievementLevelId, array $levelIds, int $targetId): bool;

    public function assignAchievement(
        int $userId,
        AchievementLevel $achievementLevel,
        array $levelIds,
        array $defaultIds,
        $giftId = null
    ): void;

    public function sendAchievementNotifications(?array $notificationIds): void;

    public function setUserAchievementLevel(bool $isTest = false): void;

    public function assignAchievementLevelToUserByAdmin(int $userId, AchievementLevel $achievementLevel): bool;
}
