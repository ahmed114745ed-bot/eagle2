<?php

namespace App\Contracts;

use App\Models\Gift;
use App\Models\User;

interface UserAchievementContract
{
    public function insertCharging(User $user, $totalCoins): void;
    
    public function roomTarget(User $user, $totalCoins);
    
    public function giftTarget(Gift $gift, $total);
    
    public function getUserAchievement(User $user);
    
    public function roomAchievement(int $ownerId);
}
