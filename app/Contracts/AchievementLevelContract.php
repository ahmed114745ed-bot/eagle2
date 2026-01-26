<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AchievementLevelContract
{
    public function assignAchievementToUser(?Model $userAchievement): void;
    
    public function approveAchievement(?Model $userAchievement, ?array $notificationIds = null): array;
    
    public function getAchievement(?Model $userAchievement);
}
