<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Modules\UsersWallet\Entities\UserWallet;
use Modules\UsersWallet\Entities\WalletLog;

class WalletService
{
    /**
     * Store transaction with effect on wallet + transaction classification.
     * Uses users_wallets and wallet_logs tables (Modules\UsersWallet)
     *
     * @param int $userId
     * @param string $type  [add, cut, pending]
     * @param float $amount
     * @param string|null $transactionsType [agency_transaction, user_transaction, etc.]
     * @param string|null $description
     * @param array|string|null $descriptionData
     * @param string $message
     * @return WalletLog|null
     */
    public static function storeTransaction(
        int $userId,
        string $type,
        float $amount,
        ?string $transactionsType = null,
        ?string $description = null,
        $descriptionData = null,
        $message = 'charge'
    ): ?WalletLog {
        return DB::transaction(function () use ($userId, $type, $amount, $transactionsType, $description, $descriptionData, $message) {
            // Get or create wallet (users_wallets table)
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'cut_amount' => 0, 'pending_amount' => 0]
            );

            // Calculate before amount
            $beforeAmount = wallet_available_by_wallet($wallet);

            // Determine operation and update wallet
            $operation = null;
            $logAmount = 0;

            switch ($type) {
                case 'add':
                    $wallet->balance += $amount;
                    $operation = 'add';
                    $logAmount = $amount;
                    break;

                case 'cut':
                    $wallet->cut_amount += $amount;
                    $operation = 'subtract';
                    $logAmount = -$amount;
                    break;

                case 'pending':
                    $wallet->pending_amount += $amount;
                    // Pending updates wallet but doesn't create log entry
                    break;

                default:
                    throw new Exception("Unsupported wallet transaction type: $type");
            }

            $wallet->save();

            // Create log entry for add/cut operations only
            if ($operation !== null) {
                $relatedId = null;
                if (is_array($descriptionData)) {
                    $relatedId = $descriptionData['receiver_id'] 
                        ?? $descriptionData['agency_id'] 
                        ?? $descriptionData['target_id'] 
                        ?? null;
                }

                return WalletLog::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $userId,
                    'amount' => $logAmount,
                    'operation' => $operation,
                    'type' => $transactionsType ?? $message,
                    'before_amount' => $beforeAmount,
                    'after_amount' => wallet_available_by_wallet($wallet),
                    'related_id' => $relatedId,
                ]);
            }

            return null;
        });
    }
}