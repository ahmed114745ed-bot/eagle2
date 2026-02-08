<?php

namespace Utd\Gifts\Services;

use Utd\Gifts\Support\ModelResolver;

class BalanceService
{
    /**
     */
    public function deductFromSender($sender, int $amount): void
    {
        if (method_exists($sender, 'decrement')) {
            $sender->decrement('di', $amount);
        } elseif (property_exists($sender, 'di')) {
            $sender->di -= $amount;
            $sender->save();
        }
    }

    /**
     */
    public function hasSufficientBalance($sender, int $amount): bool
    {
        return ($sender->di ?? 0) >= $amount;
    }
}
