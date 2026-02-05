<?php

namespace Utd\Gifts\Services;

use App\Models\User;

class BalanceService
{
    /**
     * خصم الرصيد من المرسل
     */
    public function deductFromSender(User $sender, int $amount): void
    {
        $sender->decrement('di', $amount);
    }

    /**
     * التحقق من كفاية الرصيد
     */
    public function hasSufficientBalance(User $sender, int $amount): bool
    {
        return $sender->di >= $amount;
    }
}
