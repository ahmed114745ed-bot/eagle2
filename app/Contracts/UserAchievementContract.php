<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface UserAchievementContract
{
    public function insertCharging(Model $user, $totalCoins): void;

    public function roomTarget(Model $user, $totalCoins): void;

    public function giftTarget(Model $gift, $total): void;

    public function getUserAchievement(Model $user);

    public function roomAchievement(int $ownerId);
}
