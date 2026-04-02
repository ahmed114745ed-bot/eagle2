<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;
use App\Models\FairLuckWallet;

/**
 * PoolManager V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Integrates with BankruptcyProtection for wallet-aware decisions
 * 2. Prize SIZE is reduced when wallet is low, not win frequency
 * 3. Configurable wallet distribution percentages
 */
class PoolManager
{
    /**
     * Get total unified pool balance (sum of all 3 wallets).
     * Treats all wallets as ONE pool for decision-making.
     */
    public function getTotalBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET);
    }

    public function getWalletBalances(): array
    {
        return [
            'global_vault' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT),
            'medium_wallet' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET),
            'jackpot_wallet' => FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET),
        ];
    }

    /**
     * Distribute bet contribution to wallets.
     * Uses configurable distribution percentages.
     */
    public function distributeBet(float $amount): void
    {
        if ($amount <= 0) return;

        // Get configurable distribution percentages
        $globalPct = (float) FairLuckSetting::getByKey('V7_wallet_dist_global', 0.65);
        $jackpotPct = (float) FairLuckSetting::getByKey('V7_wallet_dist_jackpot', 0.20);
        $mediumPct = (float) FairLuckSetting::getByKey('V7_wallet_dist_medium', 0.15);

        $global = (int) round($amount * $globalPct);
        $jackpot = (int) round($amount * $jackpotPct);
        $medium = (int) round($amount * $mediumPct);

        if ($global > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $global);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $global, 'V7 Bet contribution', null);
        }
        if ($jackpot > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpot);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpot, 'V7 Bet contribution', null);
        }
        if ($medium > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $medium);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $medium, 'V7 Bet contribution', null);
        }
    }

    /**
     * Pay out a win using cascading fallback across wallets.
     * NEVER fails if total pool has enough - cascades between wallets.
     * 
     * V7: Integrates wallet health for smart prize sizing.
     */
    public function payout(int $amount, int $multiplier, int $userId): bool
    {
        if ($amount <= 0) return true;

        $totalBalance = $this->getTotalBalance();
        $negativeLimit = BankruptcyProtection::getNegativeLimit();
        
        // Check if we can afford this payout
        if (($totalBalance + $negativeLimit) < $amount) {
            return false;
        }

        $description = "V7 Win payout ({$multiplier}x)";

        // V7: Check wallet health for wallet selection priority
        $healthStatus = BankruptcyProtection::getHealthStatus();
        $isHealthy = $healthStatus['is_healthy'];
        $isWarning = $healthStatus['is_warning'];

        // Determine wallet order based on multiplier AND wallet health
        if ($isHealthy) {
            // Healthy wallet: Standard tier-based distribution
            if ($multiplier >= 250) {
                $walletOrder = [
                    FairLuckWallet::TYPE_JACKPOT_WALLET,
                    FairLuckWallet::TYPE_GLOBAL_VAULT,
                    FairLuckWallet::TYPE_MEDIUM_WALLET,
                ];
            } elseif ($multiplier >= 50) {
                $walletOrder = [
                    FairLuckWallet::TYPE_MEDIUM_WALLET,
                    FairLuckWallet::TYPE_GLOBAL_VAULT,
                    FairLuckWallet::TYPE_JACKPOT_WALLET,
                ];
            } else {
                $walletOrder = [
                    FairLuckWallet::TYPE_GLOBAL_VAULT,
                    FairLuckWallet::TYPE_MEDIUM_WALLET,
                    FairLuckWallet::TYPE_JACKPOT_WALLET,
                ];
            }
        } else {
            // Low wallet: Prioritize global vault to conserve jackpot/medium
            $walletOrder = [
                FairLuckWallet::TYPE_GLOBAL_VAULT,
                FairLuckWallet::TYPE_MEDIUM_WALLET,
                FairLuckWallet::TYPE_JACKPOT_WALLET,
            ];
        }

        $this->cascadePayout($amount, $walletOrder, $description, $userId);

        return true;
    }

    /**
     * Cascade payout across wallets - deduct from each in priority order.
     */
    private function cascadePayout(int $remaining, array $walletOrder, string $description, int $userId): void
    {
        foreach ($walletOrder as $walletType) {
            if ($remaining <= 0) break;

            $balance = FairLuckWallet::getRedisBalance($walletType);
            $deduct = min($remaining, max(0, $balance));

            if ($deduct > 0) {
                FairLuckWallet::decrementRedisBalance($walletType, $deduct);
                FairLuckWallet::decreaseBalance($walletType, $deduct, $description, $userId);
                $remaining -= $deduct;
            }
        }

        // Last resort: allow global vault to go negative up to limit
        if ($remaining > 0) {
            $negativeLimit = BankruptcyProtection::getNegativeLimit();
            $globalBalance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);

            if ($globalBalance + $negativeLimit >= $remaining) {
                FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $remaining);
                FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $remaining, $description . ' (buffer)', $userId);
            }
        }
    }
}
