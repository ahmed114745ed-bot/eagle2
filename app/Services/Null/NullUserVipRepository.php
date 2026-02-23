<?php

namespace App\Services\Null;

use App\Contracts\UserVipRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class NullUserVipRepository implements UserVipRepositoryContract
{
    public function findByUserId($userId)
    {
        return null;
    }

    public function getAllByUserId($userId)
    {
        return collect();
    }

    public function getAllByUserIdWithAll($userId)
    {
        return collect();
    }

    public function deleteExpireUserVip()
    {
        return true;
    }

    public function findById($id)
    {
        return null;
    }

    public function getByUserId($userId, array $additionalRelations = []): Collection
    {
        return new Collection();
    }

    public function findByIdWithOVip($id)
    {
        return null;
    }

    public function findByIdWithPack($id, $user_id)
    {
        return null;
    }

    public function togglePackUsage($pack_id, $user_id, bool $isUsed): bool
    {
        return false;
    }

    public function updateIsUsedForUser($userId)
    {
        return null;
    }

    public function updateTrueIsUsedForUser($userId)
    {
        return null;
    }

    public function updateIsUsed($userVip, $isUsed)
    {
        return null;
    }

    public function updateNumUsed($userVip)
    {
        return null;
    }

    public function updateIsUsedWithNum($userVip, $isUsed)
    {
        return null;
    }

    public function updateUserVip($userVip)
    {
        return true;
    }

    public function findByUserLevel($userId, $level, $vipId)
    {
        return null;
    }

    public function createUserVip(array $data)
    {
        return null;
    }

    public function deleteExpiredVips($userId, $level)
    {
        return 0;
    }

    public function findUserVipWithOVip($vipId)
    {
        return null;
    }

    public function updateUserVipIsUsed($userId, $isUsed)
    {
        return 0;
    }

    public function saveUserVip($userVip)
    {
        return null;
    }

    public function findUserVipById($vipId)
    {
        return null;
    }

    public function create(array $data): mixed
    {
        return null;
    }

    public function update(array $data, $id): mixed
    {
        return null;
    }
}
