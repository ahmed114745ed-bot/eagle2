<?php

namespace App\Services\Null;

use App\Contracts\LoseWinnerRewardsContract;

class NullLoseWinnerRewardsService implements LoseWinnerRewardsContract
{
    public function removePacksVip($reward, $vip, $user, $expire): void
    {
        // Null implementation - do nothing
    }
}
