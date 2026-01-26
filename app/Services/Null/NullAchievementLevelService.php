<?php

namespace App\Services\Null;

use App\Contracts\AchievementLevelContract;
use Illuminate\Database\Eloquent\Model;

class NullAchievementLevelService implements AchievementLevelContract
{
    public function assignAchievementToUser(?Model $userAchievement): void
    {
        // Do nothing when achievement feature is disabled
    }

    public function approveAchievement(?Model $userAchievement, ?array $notificationIds = null): array
    {
        return [];
    }

    public function getAchievement(?Model $userAchievement)
    {
        return null;
    }
}
