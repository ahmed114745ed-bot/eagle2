<?php

namespace App\Services\Null;

use App\Contracts\MilestoneHelperContract;
use App\Models\User;

class NullMilestoneHelperService implements MilestoneHelperContract
{
    public function grantMilestoneToUser(User|int $user, string $slug): void
    {
        // No-op: Milestone package is not installed
    }

    public function removeReward(User|int $user, string $slug): void
    {
        // No-op: Milestone package is not installed
    }
}
