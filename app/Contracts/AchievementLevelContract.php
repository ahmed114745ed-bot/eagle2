<?php

namespace App\Contracts;

use Utd\Achievements\Entities\UserAchievement;

interface AchievementLevelContract
{
    public function assignAchievementToUser(?UserAchievement $userAchievement): void;
    
    public function approveAchievement(?UserAchievement $userAchievement, ?array $notificationIds = null): array;
    
    public function getAchievement(UserAchievement $userAchievement);
}
