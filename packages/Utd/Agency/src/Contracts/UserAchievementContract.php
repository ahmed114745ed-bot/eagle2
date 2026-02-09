<?php

namespace Utd\Agency\Contracts;

use App\Models\Gift;
use App\Models\User;

interface UserAchievementContract
{
    /**
     * Insert charging achievement for user
     *
     * @param User $user User model instance
     * @param int|float $totalCoins Total coins to charge
     * @return void
     */
    public function insertCharging(User $user, $totalCoins): void;
    
    /**
     * Get room target achievement
     *
     * @param User $user User model instance
     * @param int|float $totalCoins Total coins
     * @return mixed
     */
    public function roomTarget(User $user, $totalCoins);
    
    /**
     * Get gift target achievement
     *
     * @param Gift $gift Gift model instance
     * @param int|float $total Total amount
     * @return mixed
     */
    public function giftTarget(Gift $gift, $total);
    
    /**
     * Get user achievement data
     *
     * @param User $user User model instance
     * @return mixed
     */
    public function getUserAchievement(User $user);
    
    /**
     * Get room achievement by owner ID
     *
     * @param int $ownerId Room owner ID
     * @return mixed
     */
    public function roomAchievement(int $ownerId);
}
