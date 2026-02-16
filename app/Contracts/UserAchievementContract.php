<?php

namespace App\Contracts;

use App\Models\Gift;
use App\Models\User;

interface UserAchievementContract
{
    /**
     * Insert charging achievement for user
     */
    public function insertCharging(User $user, $totalCoins): void;

    /**
     * Get room target achievement
     */
    public function roomTarget(User $user, $totalCoins);

    /**
     * Get gift target achievement
     */
    public function giftTarget(Gift $gift, $total);

    /**
     * Get user achievement data
     */
    public function getUserAchievement(User $user);

    /**
     * Get room achievement by owner ID
     */
    public function roomAchievement(int $ownerId);
}
