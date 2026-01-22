<?php

namespace App\Services\Null;

use App\Contracts\AchievementLevelContract;
use Utd\Achievements\Entities\UserAchievement;

class NullAchievementLevelService implements AchievementLevelContract
{
    public function assignAchievementToUser(?UserAchievement $userAchievement): void
    {
        // Do nothing when achievement feature is disabled
    }

    public function approveAchievement(?UserAchievement $userAchievement, ?array $notificationIds = null): array
    {
        return [];
    }

    public function getAchievement(UserAchievement $userAchievement)
    {
        return null;
    }
}
