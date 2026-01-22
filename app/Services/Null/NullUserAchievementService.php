<?php

namespace App\Services\Null;

use App\Contracts\UserAchievementContract;
use Illuminate\Database\Eloquent\Model;

class NullUserAchievementService implements UserAchievementContract
{
    public function insertCharging(Model $user, $totalCoins): void
    {
        // No-op
    }

    public function roomTarget(Model $user, $totalCoins): void
    {
        // No-op
    }

    public function giftTarget(Model $gift, $total): void
    {
        // No-op
    }

    public function getUserAchievement(Model $user)
    {
        return collect();
    }

    public function roomAchievement(int $ownerId)
    {
        return collect();
    }
}
