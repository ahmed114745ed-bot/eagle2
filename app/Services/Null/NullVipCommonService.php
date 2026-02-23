<?php

namespace App\Services\Null;

use App\Contracts\VipCommonContract;
use App\Models\User;

class NullVipCommonService implements VipCommonContract
{
    public function createUserVip(
        $vip,
        User $user,
        int $expire = 0,
        $dashUserId = 0,
        $typeSend = '',
        $qty = 1,
        $senderId = 0,
        $total = 0,
        $receiveType = 'not-sending',
        $isUsed = null,
        $sendNotification = 1
    ): bool {
        return false;
    }

    public function handleVipActivation($userVip): void
    {
        // No-op
    }

    public function deactivateVip($vip): void
    {
        // No-op
    }

    public function unUsePack($type, $user)
    {
        // No-op
    }

    public function userDress($ware, $user, $isUsed)
    {
        // No-op
    }

    public function removeVipFromUser($user, $id, $receiveType)
    {
        // No-op
    }
}
