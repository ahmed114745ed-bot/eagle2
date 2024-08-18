<?php

namespace App\Repositories;

use App\Models\UserVip;

class UserVipRepository
{
    public function createUserVip(array $data)
    {
        return UserVip::create($data);
    }

    public function deleteExpiredVips($userId, $level)
    {
        return UserVip::where('user_id', $userId)
            ->where('level', '<=', $level)
            ->delete();
    }

    public function findUserVipWithOVip($vipId)
    {
        return UserVip::with('OVip')->has('OVip')->find($vipId);
    }

    public function updateUserVipIsUsed($userId, $isUsed)
    {
        return UserVip::where('user_id', $userId)->update(['is_used' => $isUsed]);
    }

    public function saveUserVip($userVip)
    {
        $userVip->save();
    }

    public function findUserVipById($vipId)
    {
        return UserVip::find($vipId);
    }
}