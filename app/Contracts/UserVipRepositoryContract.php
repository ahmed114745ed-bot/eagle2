<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface UserVipRepositoryContract
{
    public function findByUserId($userId);

    public function getAllByUserId($userId);

    public function getAllByUserIdWithAll($userId);

    public function deleteExpireUserVip();

    public function findById($id);

    public function getByUserId($userId, array $additionalRelations = []): Collection;

    public function findByIdWithOVip($id);

    public function findByIdWithPack($id, $user_id);

    public function togglePackUsage($pack_id, $user_id, bool $isUsed): bool;

    public function updateIsUsedForUser($userId);

    public function updateTrueIsUsedForUser($userId);

    public function updateIsUsed($userVip, $isUsed);

    public function updateNumUsed($userVip);

    public function updateIsUsedWithNum($userVip, $isUsed);

    public function updateUserVip($userVip);

    public function findByUserLevel($userId, $level, $vipId);

    public function createUserVip(array $data);

    public function deleteExpiredVips($userId, $level);

    public function findUserVipWithOVip($vipId);

    public function updateUserVipIsUsed($userId, $isUsed);

    public function saveUserVip($userVip);

    public function findUserVipById($vipId);

    public function create(array $data): mixed;

    public function update(array $data, $id): mixed;
}
