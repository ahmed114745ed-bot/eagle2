<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;
use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Log;

/**
 * BankruptcyProtection V7: Single Unified Wallet
 * 
 * Key principles:
 * 1. Wallet protection operates in USD (admin-configurable, project-agnostic)
 * 2. Short-term wallet dips are acceptable — system recovers through volume
 * 3. Prize SIZE is reduced when wallet is low, not win frequency
 * 4. Users stay engaged — they still win, just smaller amounts
 * 5. Emergency mode only kicks in at true critical levels
 * 
 * V7 uses a SINGLE wallet (global_vault) — no more 3-wallet split.
 */
class BankruptcyProtection
{
    /**
     * Get the coin to USD conversion rate
     */
    public static function getCoinToUsdRate(): float
    {
        return FairLuckSetting::getCoinToUsdRate();
    }

    /**
     * Get wallet thresholds in USD and convert to coins
     */
    public static function getHealthyThreshold(): int
    {
        return FairLuckSetting::usdToCoins(FairLuckSetting::getHealthyWalletUsd());
    }

    public static function getWarningThreshold(): int
    {
        return FairLuckSetting::usdToCoins(FairLuckSetting::getWarningWalletUsd());
    }

    public static function getCriticalThreshold(): int
    {
        return FairLuckSetting::usdToCoins(FairLuckSetting::getCriticalWalletUsd());
    }

    public static function getNegativeLimit(): int
    {
        return FairLuckSetting::usdToCoins(FairLuckSetting::getMaxNegativeWalletUsd());
    }

    /**
     * Get the maximum payout as percentage of pool balance
     */
    public static function getMaxPayoutPercentage(): float
    {
        return FairLuckSetting::getMaxSingleWinPercentage();
    }

    /**
     * Get wallet health status with USD-aware thresholds
     * V7: Uses single global_vault wallet
     */
    public static function getHealthStatus(): array
    {
        $totalBalance = self::getTotalPoolBalance();
        $healthy = self::getHealthyThreshold();
        $warning = self::getWarningThreshold();
        $critical = self::getCriticalThreshold();
        $negativeLimit = self::getNegativeLimit();
        $balanceUsd = FairLuckSetting::coinsToUsd($totalBalance);

        $healthPercentage = $healthy > 0 ? ($totalBalance / $healthy) * 100 : 100;

        return [
            'total_balance' => $totalBalance,
            'total_balance_usd' => $balanceUsd,
            'healthy_threshold' => $healthy,
            'warning_threshold' => $warning,
            'critical_threshold' => $critical,
            'negative_limit' => $negativeLimit,
            'health_percentage' => round($healthPercentage, 2),
            'is_critical' => $totalBalance < $critical,
            'is_warning' => $totalBalance < $warning && $totalBalance >= $critical,
            'is_healthy' => $totalBalance >= $healthy,
            'status' => self::getStatus($totalBalance, $healthy, $warning, $critical),
        ];
    }

    /**
     * Get human-readable status
     */
    private static function getStatus(int $balance, int $healthy, int $warning, int $critical): string
    {
        if ($balance >= $healthy) {
            return 'healthy';
        } elseif ($balance >= $warning) {
            return 'moderate'; // Between healthy and warning
        } elseif ($balance >= $critical) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Get total pool balance — V7: single global_vault wallet
     */
    public static function getTotalPoolBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
    }

    /**
     * Get max multiplier allowed based on wallet health
     * Philosophy: Reduce prize SIZE, not win frequency
     */
    public static function getMaxMultiplierForHealth(): int
    {
        $balance = self::getTotalPoolBalance();
        $healthy = self::getHealthyThreshold();
        $warning = self::getWarningThreshold();
        $critical = self::getCriticalThreshold();

        if ($balance >= $healthy) {
            // Wallet healthy: Full generosity — all multipliers available
            return FairLuckSetting::getWalletHealthyMaxMultiplier();
        } elseif ($balance >= $warning) {
            // Wallet moderate: Same win rate, but favor medium multipliers
            return FairLuckSetting::getWalletModerateMaxMultiplier();
        } elseif ($balance >= $critical) {
            // Wallet low: Same win rate, only small-to-medium wins
            return FairLuckSetting::getWalletLowMaxMultiplier();
        } else {
            // Wallet critical: Slight probability reduction, small wins only
            return FairLuckSetting::getWalletCriticalMaxMultiplier();
        }
    }

    /**
     * Get probability factor based on wallet health
     * Philosophy: Only reduce probability in true emergencies, and never below 60%
     */
    public static function getProbabilityReductionFactor(): float
    {
        $balance = self::getTotalPoolBalance();
        $critical = self::getCriticalThreshold();
        $negativeLimit = self::getNegativeLimit();
        $minProb = FairLuckSetting::getMinProbabilityWhenLow();

        // If balance is above critical, no reduction
        if ($balance >= $critical) {
            return 1.0;
        }

        // If balance is below critical but above negative limit, gradual reduction
        $range = $critical - (-$negativeLimit);
        $position = $balance - (-$negativeLimit);
        $ratio = $range > 0 ? max(0, $position / $range) : 0;

        // Return between minProb and 1.0 based on how close we are to the negative limit
        return $minProb + ($ratio * (1.0 - $minProb));
    }

    /**
     * Get maximum safe payout amount
     */
    public static function getMaxSafePayoutAmount(): int
    {
        $totalBalance = self::getTotalPoolBalance();
        $maxPercentage = self::getMaxPayoutPercentage();
        $negativeLimit = self::getNegativeLimit();

        // Maximum payout is configurable % of pool balance
        $maxByPercentage = (int) round($totalBalance * $maxPercentage);

        // But never exceed the negative limit buffer
        $maxByLimit = $totalBalance + $negativeLimit;

        return min($maxByPercentage, $maxByLimit);
    }

    /**
     * Validate and cap payout based on wallet health
     */
    public static function validateAndCapPayout(int $requestedPayout): int
    {
        $totalBalance = self::getTotalPoolBalance();
        $negativeLimit = self::getNegativeLimit();
        $effectiveBalance = $totalBalance + $negativeLimit;

        // If requested exceeds effective balance, cap it
        if ($requestedPayout > $effectiveBalance) {
            $capped = max(0, $effectiveBalance);
            return $capped;
        }

        // Apply max payout percentage cap
        $maxSafe = self::getMaxSafePayoutAmount();
        if ($requestedPayout > $maxSafe) {
            return $maxSafe;
        }

        return $requestedPayout;
    }

    /**
     * Check if system can afford a payout
     */
    public static function canAffordPayout(int $payoutAmount): bool
    {
        $totalBalance = self::getTotalPoolBalance();
        $negativeLimit = self::getNegativeLimit();
        $effectiveBalance = $totalBalance + $negativeLimit;

        return $effectiveBalance >= $payoutAmount;
    }

    /**
     * Log critical event
     */
    public static function logCriticalEvent(string $event, array $context = []): void
    {
        // Logging disabled for production performance
    }

    /**
     * Check if a user is exploiting the system (intentional losses)
     */
    public static function isUserExploiting(int $userId, float $totalSpent, float $totalReceived): bool
    {
        // Pattern 1: User has spent significant amount but received almost nothing
        if ($totalSpent > 100000 && $totalReceived < ($totalSpent * 0.3)) {
            return true;
        }

        // Pattern 2: User has extreme RTP variance
        $rtp = $totalSpent > 0 ? $totalReceived / $totalSpent : 0;
        if ($rtp > 2.0 || $rtp < 0.2) {
            return true;
        }

        return false;
    }
}
