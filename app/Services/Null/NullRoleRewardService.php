<?php

namespace App\Services\Null;

use App\Contracts\RoleRewardContract;
use App\Models\User;

class NullRoleRewardService implements RoleRewardContract
{
    public function giveRoleRewards(User $user, int $roleId, $slug = null): void
    {
    }

    public function revokeRoleRewards(User $user, int $roleId, $slug = null): void
    {
    }

    public function revoke(User $user, string $receiveType): void
    {
    }

    public function revokeRewardsFromAllUsersForRole(int $roleId, string $slug = null): void
    {
    }

    public function syncRewardsForRole(int $roleId, string $slug = null): void
    {
    }

    public function revokeSpecificRewardFromAllUsers(
        int $roleId,
        string $slug,
        int $rewardId,
        string $rewardableType,
        int $rewardableId
    ): void {
    }
}
