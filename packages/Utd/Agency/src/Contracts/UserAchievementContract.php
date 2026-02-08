<?php

namespace Utd\Agency\Contracts;

interface UserAchievementContract
{
    /**
     * Insert charging achievement for user
     *
     * @param mixed $user User model instance
     * @param int|float $totalCoins Total coins to charge
     * @return void
     */
    public function insertCharging($user, $totalCoins): void;
    
    /**
     * Get room target achievement
     *
     * @param mixed $user User model instance
     * @param int|float $totalCoins Total coins
     * @return mixed
     */
    public function roomTarget($user, $totalCoins);
    
    /**
     * Get gift target achievement
     *
     * @param mixed $gift Gift model instance
     * @param int|float $total Total amount
     * @return mixed
     */
    public function giftTarget($gift, $total);
    
    /**
     * Get user achievement data
     *
     * @param mixed $user User model instance
     * @return mixed
     */
    public function getUserAchievement($user);
    
    /**
     * Get room achievement by owner ID
     *
     * @param int $ownerId Room owner ID
     * @return mixed
     */
    public function roomAchievement(int $ownerId);
}
