<?php

use Utd\UsersWallet\Facades\Wallet;

if (!function_exists('Wallet')) {
    /**
     * Get the wallet service instance.
     */
    function Wallet(): Utd\UsersWallet\Contracts\WalletServiceInterface
    {
        return app(Utd\UsersWallet\Contracts\WalletServiceInterface::class);
    }
}

if (!function_exists('wallet_store_transaction')) {
    /**
     * Store a wallet transaction.
     */
    function wallet_store_transaction(
        int $userId,
        string $operation,
        float $amount,
        string $type,
        string $description,
        array $descriptionData = [],
        string $uniqueId = null
    ): bool {
        return Wallet::storeTransaction($userId, $operation, $amount, $type, $description, $descriptionData, $uniqueId);
    }
}

if (!function_exists('wallet_recalculate_balance')) {
    /**
     * Recalculate user wallet balance.
     */
    function wallet_recalculate_balance(int $userId): float
    {
        return Wallet::recalculateWalletBalance($userId);
    }
}

if (!function_exists('wallet_validate')) {
    /**
     * Validate user wallet.
     */
    function wallet_validate(int $userId)
    {
        return Wallet::validateWallet($userId);
    }
}
