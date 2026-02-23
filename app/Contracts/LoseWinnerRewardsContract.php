<?php

namespace App\Contracts;

interface LoseWinnerRewardsContract
{
    /**
     * Remove VIP packs from a user when they lose winner rewards.
     *
     * @param mixed $reward The winner reward being removed
     * @param mixed $vip The user's VIP entity
     * @param mixed $user The user losing the reward
     * @param int|null $expire Days until expiration
     * @return void
     */
    public function removePacksVip($reward, $vip, $user, $expire): void;
}
