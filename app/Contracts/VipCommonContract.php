<?php

namespace App\Contracts;

use App\Models\User;

interface VipCommonContract
{
    /**
     * Create a new VIP for a user.
     *
     * @param mixed $vip
     * @param User $user
     * @param int $expire
     * @param int $dashUserId
     * @param string $typeSend
     * @param int $qty
     * @param int $senderId
     * @param int $total
     * @param string $receiveType
     * @param mixed $isUsed
     * @param int $sendNotification
     * @return bool
     */
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
    ): bool;

    /**
     * Handle VIP activation for a user.
     *
     * @param mixed $userVip
     * @return void
     */
    public function handleVipActivation($userVip): void;

    /**
     * Deactivate a VIP.
     *
     * @param mixed $vip
     * @return void
     */
    public function deactivateVip($vip): void;

    /**
     * Unuse pack by type for a user.
     *
     * @param mixed $type
     * @param mixed $user
     * @return void
     */
    public function unUsePack($type, $user);

    /**
     * Update user dress fields based on ware type.
     *
     * @param mixed $ware
     * @param mixed $user
     * @param mixed $isUsed
     * @return void
     */
    public function userDress($ware, $user, $isUsed);

    /**
     * Remove VIP from a user.
     *
     * @param mixed $user
     * @param mixed $id
     * @param mixed $receiveType
     * @return void
     */
    public function removeVipFromUser($user, $id, $receiveType);
}
