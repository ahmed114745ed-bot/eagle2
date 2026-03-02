<?php

namespace App\Contracts;

use App\Models\User;

interface MilestoneHelperContract
{
    public function grantMilestoneToUser(User|int $user, string $slug): void;

    public function removeReward(User|int $user, string $slug): void;
}
