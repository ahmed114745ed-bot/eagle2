<?php

namespace App\Services;

use App\Models\UserWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * Store transaction with effect on wallet + transaction classification.
     *
     * @param int $userId
     * @param string $type  [add, cut, pending]
     * @param float $amount
     * @param string|null $transactionsType [agency_salary, host_salary, etc.]
     * @param string|null $description
     * @param array|string|null $descriptionData
     * @return WalletTransaction
     */
    public static function storeTransaction(
        int $userId,
        string $type,
        float $amount,
        ?string $transactionsType = null,
        ?string $description = null,
        $descriptionData = null,
        $message = 'transfer_to_user'
    ): WalletTransaction {
        return DB::transaction(function () use ($userId, $type, $amount, $transactionsType, $description, $descriptionData ,$message) {
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $userId],
                ['value' => 0, 'cut_amount' => 0, 'pending_value' => 0]
            );
            
            switch ($type) {
                case 'add':
                    $wallet->value += $amount;
                    break;

                case 'cut':
                    $wallet->cut_amount += $amount;
                    $amount = -$amount;
                    break;

                case 'pending':
                    $wallet->pending_value += $amount;
                    break;

                default:
                    throw new Exception("Unsupported wallet transaction type: $type");
            }
            
            $wallet->save();
            return WalletTransaction::create([
                'user_id' => $userId,
                'type' => $type,
                'transactions_type' => $transactionsType,
                'value' => $amount,
                'description' => $description,
                'description_data' => is_array($descriptionData) ? json_encode($descriptionData) : $descriptionData,
                'message' => $message,
           
            ]);
        });
    }
}