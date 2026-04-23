<?php

namespace Utd\UsersWallet\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Utd\UsersWallet\Entities\UserWallet;
use Utd\UsersWallet\Entities\WalletLog;

class WalletTransactionService
{
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
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'cut_amount' => 0, 'pending_amount' => 0]
            );

            $beforeAmount = wallet_available_by_wallet($wallet);

            $operation = null;
            $logAmount = 0;

            switch ($type) {
                case 'add':
                    $wallet->balance += $amount;
                    $operation = 'add';
                    $logAmount = $amount;
                    break;

                case 'cut':
                    $availableBalance = wallet_available_by_wallet($wallet);

                    $wallet->cut_amount += $amount;
                    $operation = 'subtract';
                    $logAmount = -$amount;
                    break;

                case 'pending':
                    $wallet->pending_amount += $amount;
                    break;

                default:
                    throw new Exception("Unsupported wallet transaction type: $type");
            }

            $wallet->save();

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

    public static function recalculateWalletBalance(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            $wallet = UserWallet::where('user_id', $userId)->first();

            if (!$wallet) {
                // throw new Exception("Wallet not found for user: $userId");
            }

            $oldBalance = $wallet->balance;
            $oldCutAmount = $wallet->cut_amount;
            $oldPendingAmount = $wallet->pending_amount;
            $oldAvailable = wallet_available_by_wallet($wallet);

            $logs = WalletLog::where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->get();

            $calculatedBalance = 0;
            $calculatedCutAmount = 0;

            foreach ($logs as $log) {
                $amount = abs($log->amount);

                if ($log->operation === 'add') {
                    $calculatedBalance += $amount;
                } elseif ($log->operation === 'subtract') {
                    $calculatedCutAmount += $amount;
                }
            }

            $wallet->balance = $calculatedBalance;
            $wallet->cut_amount = $calculatedCutAmount;
            $wallet->save();

            $newAvailable = wallet_available_by_wallet($wallet);

            return [
                'user_id' => $userId,
                'old' => [
                    'balance' => $oldBalance,
                    'cut_amount' => $oldCutAmount,
                    'pending_amount' => $oldPendingAmount,
                    'available' => $oldAvailable,
                ],
                'new' => [
                    'balance' => $wallet->balance,
                    'cut_amount' => $wallet->cut_amount,
                    'pending_amount' => $wallet->pending_amount,
                    'available' => $newAvailable,
                ],
                'corrected' => ($oldBalance != $wallet->balance || $oldCutAmount != $wallet->cut_amount),
                'logs_count' => $logs->count(),
            ];
        });
    }

    public static function validateWallet(int $userId): array
    {
        $wallet = UserWallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'valid' => false,
                'issues' => ['Wallet not found']
            ];
        }

        $issues = [];

        if ($wallet->balance < 0) {
            $issues[] = "Negative balance: {$wallet->balance}";
        }

        if ($wallet->cut_amount < 0) {
            $issues[] = "Negative cut_amount: {$wallet->cut_amount}";
        }

        if ($wallet->pending_amount < 0) {
            $issues[] = "Negative pending_amount: {$wallet->pending_amount}";
        }

        if ($wallet->cut_amount > $wallet->balance) {
            $issues[] = "Cut amount ({$wallet->cut_amount}) exceeds balance ({$wallet->balance})";
        }

        $available = wallet_available_by_wallet($wallet);
        if ($available < 0) {
            $issues[] = "Negative available balance: {$available}";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'wallet' => [
                'balance' => $wallet->balance,
                'cut_amount' => $wallet->cut_amount,
                'pending_amount' => $wallet->pending_amount,
                'available' => $available,
            ]
        ];
    }
}
