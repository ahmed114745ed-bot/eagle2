<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckWallet;

/**
 * BankruptcyProtection V7: Diagnostic-only.
 *
 * All active wallet protection logic has moved to MultiplierTable.
 * This class only provides health status for logging/diagnostics.
 */
class BankruptcyProtection
{
    public static function getHealthStatus(): array
    {
        $balance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        $table = new MultiplierTable();

        return [
            'total_balance' => $balance,
            'status' => $table->getZone($balance),
            'wallet_factor' => $table->walletFactor($balance),
        ];
    }

    public static function getNegativeLimit(): int
    {
        return 0; // No longer used — hard gate in MultiplierTable prevents bankruptcy
    }

    public static function getMaxMultiplierForHealth(): int
    {
        return 1000; // No longer used — MultiplierTable handles via weight adjustment + jackpot gate
    }
}
