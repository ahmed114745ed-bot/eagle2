<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Support\PackageHelper;
use Utd\UsersWallet\Entities\UserWallet;
use Utd\UsersWallet\Entities\WalletLog;

class WalletService
{
    /**
     * Store transaction with effect on wallet + transaction classification.
     * Delegates to the UsersWallet package.
     */
    public static function storeTransaction(
        int $userId,
        string $type,
        float $amount,
        ?string $transactionsType = null,
        ?string $description = null,
        $descriptionData = null,
        $message = 'charge'
    ) {
        if (!PackageHelper::isInstalled('usersWallet')) {
            return null;
        }

        // Map legacy 'cut' to package 'subtract'
        $operation = ($type === 'cut') ? 'subtract' : $type;

        return \Utd\UsersWallet\Facades\Wallet::storeTransaction(
            $userId,
            $operation,
            $amount,
            $transactionsType ?? $message,
            $description ?? '',
            is_array($descriptionData) ? $descriptionData : [],
            $message
        );
    }

    /**
     * Recalculate wallet balance from logs.
     */
    public static function recalculateWalletBalance(int $userId): array
    {
        if (!PackageHelper::isInstalled('usersWallet')) {
            return [];
        }

        $result = \Utd\UsersWallet\Facades\Wallet::recalculateWalletBalance($userId);

        // The package returns float (new balance), but legacy expects array
        // We'll perform a basic wrapper or just return the float if acceptable.
        // Since we want to maintain compatibility with array expectations:
        return [
            'user_id' => $userId,
            'new_balance' => $result
        ];
    }

    /**
     * Validate wallet integrity.
     */
    public static function validateWallet(int $userId): array
    {
        if (!PackageHelper::isInstalled('usersWallet')) {
            return ['valid' => false, 'issues' => ['Package not installed']];
        }

        return \Utd\UsersWallet\Facades\Wallet::validateWallet($userId);
    }
}