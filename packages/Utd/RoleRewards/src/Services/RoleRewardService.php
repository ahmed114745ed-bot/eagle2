<?php

namespace Utd\RoleRewards\Services;

use App\Contracts\RoleRewardContract;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Helpers\UserCommon;
use Utd\Vip\Entities\OVip;
use Utd\RoleRewards\Entities\RoleReward;
use App\Models\UserHistoryReward;
use Utd\Achievements\Entities\UserAchievementLevel;
use Utd\Badge\Entities\Badge;
use App\Support\PackageHelper;

class RoleRewardService implements RoleRewardContract
{
    public function giveRoleRewards(User $user, int $roleId, $slug = null): void
    {
        $rewards = RoleReward::where('role_id', $roleId)->get();

        foreach ($rewards as $reward) {
            $exists = UserHistoryReward::where([
                'user_id'         => $user->id,
                'receive_type'    => "Role:$roleId",
                'rewardable_id'   => $reward->rewardable_id,
                'rewardable_type' => $reward->rewardable_type,
            ])->where('is_deleted', 0)->exists();

            if (! $exists) {
                UserHistoryReward::create([
                    'user_id'         => $user->id,
                    'sub_type'        => 'roles',
                    'receive_type'    => "Role:$roleId",
                    'rewardable_id'   => $reward->rewardable_id,
                    'rewardable_type' => $reward->rewardable_type,
                    'extra'           => ['expire' => $reward->expire],
                ]);

                $this->applyReward($user, $reward, "Role:$roleId");
            }
        }
    }

    public function revokeRoleRewards(User $user, int $roleId, $slug = null): void
    {
        $rewards = UserHistoryReward::where('user_id', $user->id)
            ->where('receive_type', "Role:$roleId")
            ->get();

        foreach ($rewards as $reward) {
            $this->removeReward($user, $reward);
            $reward->update(['is_deleted' => 1]);
            $reward->delete();
        }
    }

    public function revoke(User $user, string $receiveType): void
    {
        $rewards = UserHistoryReward::where('user_id', $user->id)
            ->where('receive_type', $receiveType)
            ->get();

        foreach ($rewards as $reward) {
            $this->removeReward($user, $reward);
            $reward->delete();
        }
    }

    public function revokeRewardsFromAllUsersForRole(int $roleId, string $slug = null): void
    {
        $dashboardUsers = \Encore\Admin\Auth\Database\Administrator::whereHas('roles', function ($q) use ($roleId) {
            $q->where('id', $roleId);
        })->get();

        $appIds = $dashboardUsers->pluck('app_id')->filter()->unique();

        if ($appIds->isEmpty()) {
            return;
        }

        $users = User::whereIn('id', $appIds)->get();

        foreach ($users as $user) {
            $this->revokeRoleRewards($user, $roleId, $slug);
        }
    }

    public function syncRewardsForRole(int $roleId, string $slug = null): void
    {
        $dashboardUsers = \Encore\Admin\Auth\Database\Administrator::whereHas('roles', function ($q) use ($roleId) {
            $q->where('id', $roleId);
        })->get();

        $appIds = $dashboardUsers->pluck('app_id')->filter()->unique();
        if ($appIds->isEmpty()) return;

        $users = User::whereIn('id', $appIds)->get();

        foreach ($users as $user) {
            $this->giveRoleRewards($user, $roleId, $slug);
        }
    }

    public function revokeSpecificRewardFromAllUsers(
        int $roleId,
        string $slug,
        int $rewardId,
        string $rewardableType,
        int $rewardableId
    ): void {
        $dashboardUsers = \Encore\Admin\Auth\Database\Administrator::whereHas('roles', function ($q) use ($roleId) {
            $q->where('id', $roleId);
        })->get();

        $appIds = $dashboardUsers->pluck('app_id')->filter()->unique();

        if ($appIds->isEmpty()) {
            return;
        }

        $users = User::whereIn('id', $appIds)->get();

        foreach ($users as $user) {
            $this->revokeOneReward($user, $roleId, $slug, $rewardableType, $rewardableId);
        }
    }

    protected function applyReward(User $user, $reward, $receiveType): void
    {
        if ($reward->type === "vip") {
            if (PackageHelper::isInstalled('vip')) {
                $vip = OVip::find($reward->rewardable_id);
                UserCommon::addVipToUser($user, $vip, $reward->expire, null, $receiveType);
            }
        } elseif ($reward->type === "ware") {
            $ware = Ware::find($reward->rewardable_id);
            UserCommon::addEvintsWareToUser($user, $ware, $reward->expire, null, $receiveType);
        } elseif ($reward->type === "achievement") {
            if (class_exists(UserAchievementLevel::class)) {
                UserAchievementLevel::create([
                    'user_id'      => $user->id,
                    'custom_image' => $reward->reward_achievement,
                ]);
            }
        } elseif ($reward->type === "badge") {
            if (PackageHelper::isInstalled('badge')) {
                Common::userBadge($user->id, $reward->rewardable_id, $reward->expire, $receiveType);
            }
        }
    }

    protected function removeReward(User $user, UserHistoryReward $reward): void
    {
        if ($reward->rewardable_type === $this->mapTypeToModel('vip')) {
            UserCommon::removeVipFromUser($user, $reward->rewardable_id, $reward->receive_type);
        } elseif ($reward->rewardable_type === $this->mapTypeToModel('ware')) {
            UserCommon::removeEventsWareFromUser($user, $reward->rewardable_id, $reward->receive_type);
        } elseif ($reward->rewardable_type === $this->mapTypeToModel('achievement')) {
            if (class_exists(UserAchievementLevel::class)) {
                UserAchievementLevel::where('user_id', $user->id)
                    ->where('custom_image', $reward->rewardable_id)
                    ->where('receive_type', $reward->receive_type)
                    ->delete();
            }
        } elseif ($reward->rewardable_type === $this->mapTypeToModel('badge')) {
            UserCommon::removeBadgeFromUser($user, $reward->rewardable_id, $reward->receive_type);
        }
    }

    protected function mapTypeToModel(string $type): string
    {
        return match ($type) {
            'coins'       => 'coins',
            'vip'         => OVip::class,
            'ware'        => Ware::class,
            'achievement' => UserAchievementLevel::class,
            'badge'       => Badge::class,
            default       => $type,
        };
    }

    protected function revokeOneReward($user, int $roleId, string $slug, string $rewardableType, int $rewardableId): void
    {
        $rewards = UserHistoryReward::where('user_id', $user->id)
            ->where('receive_type', "Role:$slug:$roleId")
            ->where('rewardable_type', $rewardableType)
            ->where('rewardable_id', $rewardableId)
            ->get();

        foreach ($rewards as $reward) {
            $this->removeReward($user, $reward);
            $reward->delete();
        }
    }
}
