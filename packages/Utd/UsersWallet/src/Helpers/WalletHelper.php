<?php

namespace Utd\UsersWallet\Helpers;

use Utd\UsersWallet\Entities\UserWithdrawal;
use Utd\UsersWallet\Facades\Wallet;
use Illuminate\Support\Facades\DB;

class WalletHelper
{
    /**
     * Create a withdrawal request and move funds to pending.
     */
    public static function createWithdrawal(int $userId, float $amount, array $meta = [])
    {
        return DB::transaction(function () use ($userId, $amount, $meta) {
            // Move amount to pending in the wallet
            Wallet::storeTransaction(
                $userId,
                'pending',
                $amount,
                'withdrawal_request',
                'Withdrawal request created',
                $meta
            );

            return UserWithdrawal::create([
                'user_id' => $userId,
                'amount' => $amount,
                'status' => 'pending',
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Update multiple balances based on diffs between old and new data.
     * Often used in bulk updates or background jobs.
     */
    public static function addAllBalancesByDiffs(
        int $userId,
        array $newData,
        array $oldData,
        ?int $agencyId = null,
        string $type = 'system',
        $target_id = null
    ): void {
        $amountKeys = ['balance', 'coins', 'usd', 'di', 'diamonds']; // common fields

        foreach ($newData as $key => $value) {
            if (!in_array($key, $amountKeys))
                continue;

            $oldValue = $oldData[$key] ?? 0;
            $diff = $value - $oldValue;

            if ($diff != 0) {
                $operation = $diff > 0 ? 'add' : 'subtract';

                Wallet::storeTransaction(
                    $userId,
                    $operation,
                    abs($diff),
                    $type,
                    "Automated balance update for $key",
                    [
                        'field' => $key,
                        'old_value' => $oldValue,
                        'new_value' => $value,
                        'agency_id' => $agencyId,
                        'target_id' => $target_id
                    ]
                );
            }
        }
    }
}
