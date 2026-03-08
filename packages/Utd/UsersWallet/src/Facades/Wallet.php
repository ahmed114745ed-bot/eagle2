<?php

namespace Utd\UsersWallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool storeTransaction(int $userId, string $operation, float $amount, string $type, string $description, array $descriptionData = [], string $uniqueId = null)
 * @method static float recalculateWalletBalance(int $userId)
 * @method static mixed validateWallet(int $userId)
 *
 * @see \Utd\UsersWallet\Services\WalletService
 */
class Wallet extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}
