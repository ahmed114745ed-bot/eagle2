<?php

namespace Utd\UsersWallet\Contracts;

interface WalletServiceInterface
{
    /**
     * Store a wallet transaction.
     *
     * @param int $userId
     * @param string $operation ('add', 'subtract', 'cut', 'pending')
     * @param float $amount
     * @param string $type ('charges', 'targets', 'gifts', 'exchange', 'transfer', etc.)
     * @param string $description (key for translation)
     * @param array $descriptionData (dynamic data for translation)
     * @param string|null $uniqueId (optional unique identifier for the transaction)
     * @return bool
     */
    public function storeTransaction(
        int $userId,
        string $operation,
        float $amount,
        string $type,
        string $description,
        array $descriptionData = [],
        string $uniqueId = null
    ): bool;

    /**
     * Recalculate user wallet balance based on logs.
     *
     * @param int $userId
     * @return float
     */
    public function recalculateWalletBalance(int $userId): float;

    /**
     * Validate and ensure user has a wallet.
     *
     * @param int $userId
     * @return mixed
     */
    public function validateWallet(int $userId);
}
