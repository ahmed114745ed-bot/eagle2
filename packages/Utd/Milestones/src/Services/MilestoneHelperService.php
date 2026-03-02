<?php

namespace Utd\Milestones\Services;

use App\Contracts\MilestoneHelperContract;
use App\Models\User;
use Utd\Milestones\Helpers\MilestoneHelper;

class MilestoneHelperService implements MilestoneHelperContract
{
    public function grantMilestoneToUser(User|int $user, string $slug): void
    {
        MilestoneHelper::grantMilestoneToUser($user, $slug);
    }

    public function removeReward(User|int $user, string $slug): void
    {
        MilestoneHelper::removeReward($user, $slug);
    }
}
