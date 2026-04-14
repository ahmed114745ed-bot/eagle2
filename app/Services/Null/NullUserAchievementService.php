<?php

namespace App\Services\Null;

use App\Contracts\UserAchievementContract;
use Utd\Gifts\Entities\Gift;
use App\Models\User;

class NullUserAchievementService implements UserAchievementContract
{
    public function insertCharging(User $user, $totalCoins): void
    {
        // Do nothing when achievement feature is disabled
    }

    public function roomTarget(User $user, $totalCoins)
    {
        // Do nothing when achievement feature is disabled
    }

    public function giftTarget(Gift $gift, $total)
    {
        // Do nothing when achievement feature is disabled
    }

    public function getUserAchievement(User $user)
    {
        return collect();
    }

    public function roomAchievement(int $ownerId)
    {
        return collect();
    }
}
