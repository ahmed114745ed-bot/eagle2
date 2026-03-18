<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Log;

class WalletManager
{
    /**
     * الحصول على معلومات جميع المحافظ
     */
    public static function getAllWalletBalances(): array
    {
        return FairLuckWallet::getAllBalances();
    }

    /**
     * Unified Vault Migration: Transfer and Rebalance are no longer needed.
     * Logic removed to prevent split-liquidity operations.
     */

    /**
     * التحقق من صحة أرصدة المحافظ
     */
    public static function validateWalletIntegrity(): bool
    {
        $balances = self::getAllWalletBalances();
        
        foreach ($balances as $walletType => $balance) {
            if ($walletType === FairLuckWallet::TYPE_GLOBAL_VAULT && $balance < -FairLuckWallet::getNegativeLimit()) {
                 Log::error("Unified vault below negative limit", ['balance' => $balance]);
                 return false;
            }
            if ($walletType !== FairLuckWallet::TYPE_GLOBAL_VAULT && $balance < 0) {
                Log::error("Negative wallet balance detected", [
                    'wallet_type' => $walletType,
                    'balance' => $balance
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * تسجيل تقرير حالة المحافظ
     */
    public static function logWalletStatus(): void
    {
        $balances = self::getAllWalletBalances();
        $total = $balances['global_vault'] ?? 0;
        
        $report = [
            'unified_vault_balance' => $total,
            'wallets' => $balances,
            'note' => 'System is now using a Single Unified Vault (global_vault)',
            'status' => 'CONSOLIDATED'
        ];
        
        Log::info("FairLuck Wallets Status Report (UNIFIED)", $report);
    }
}