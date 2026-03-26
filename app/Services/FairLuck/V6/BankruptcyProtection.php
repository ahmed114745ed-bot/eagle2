<?php

namespace App\Services\FairLuck\V6;

use App\Models\FairLuckSetting;
use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Log;

/**
 * BankruptcyProtection: Ensures the system never goes bankrupt
 * 
 * Key principles:
 * 1. Minimum pool balance must be maintained (safety threshold)
 * 2. Probability is reduced when pool is low
 * 3. Maximum payout is capped based on pool health
 * 4. Negative limit is enforced strictly
 * 5. System alerts when approaching critical levels
 */
class BankruptcyProtection
{
    /**
     * Get the minimum safe pool balance (percentage of daily revenue)
     */
    public static function getMinimumSafeBalance(): int
    {
        return (int) FairLuckSetting::getByKey('bankruptcy_min_safe_balance', 100000);
    }

    /**
     * Get the critical alert threshold (when to trigger warnings)
     */
    public static function getCriticalThreshold(): int
    {
        return (int) FairLuckSetting::getByKey('bankruptcy_critical_threshold', 50000);
    }

    /**
     * Get the absolute negative limit (hard stop)
     */
    public static function getNegativeLimit(): int
    {
        return (int) FairLuckSetting::getByKey('global_vault_negative_limit', 30000);
    }

    /**
     * Get the maximum payout as percentage of pool balance
     */
    public static function getMaxPayoutPercentage(): float
    {
        return (float) FairLuckSetting::getByKey('bankruptcy_max_payout_percentage', 0.15);
    }

    /**
     * Check if system is in critical state
     */
    public static function isCritical(): bool
    {
        $totalBalance = self::getTotalPoolBalance();
        return $totalBalance < self::getCriticalThreshold();
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
     * Get the maximum safe payout amount
     */
    public static function getMaxSafePayoutAmount(): int
    {
        $totalBalance = self::getTotalPoolBalance();
        $maxPercentage = self::getMaxPayoutPercentage();
        
        // Maximum payout is 15% of pool balance
        $maxByPercentage = (int) round($totalBalance * $maxPercentage);
        
        // But never exceed the negative limit
        $negativeLimit = self::getNegativeLimit();
        $maxByLimit = $totalBalance + $negativeLimit;

        return min($maxByPercentage, $maxByLimit);
    }

    /**
     * Get total pool balance across all wallets
     */
    public static function getTotalPoolBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET)
            + FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET);
    }

    /**
     * Get health status of the system
     */
    public static function getHealthStatus(): array
    {
        $totalBalance = self::getTotalPoolBalance();
        $minSafe = self::getMinimumSafeBalance();
        $critical = self::getCriticalThreshold();
        $negativeLimit = self::getNegativeLimit();

        $healthPercentage = $minSafe > 0 ? ($totalBalance / $minSafe) * 100 : 100;

        return [
            'total_balance' => $totalBalance,
            'minimum_safe_balance' => $minSafe,
            'critical_threshold' => $critical,
            'negative_limit' => $negativeLimit,
            'health_percentage' => round($healthPercentage, 2),
            'is_critical' => $totalBalance < $critical,
            'is_safe' => $totalBalance >= $minSafe,
            'status' => self::getStatus($totalBalance, $minSafe, $critical),
        ];
    }

    /**
     * Get human-readable status
     */
    private static function getStatus(int $balance, int $minSafe, int $critical): string
    {
        if ($balance >= $minSafe) {
            return 'healthy';
        } elseif ($balance >= $critical) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Log critical event
     */
    public static function logCriticalEvent(string $event, array $context = []): void
    {   /*
        Log::critical("BANKRUPTCY_PROTECTION: {$event}", array_merge([
            'pool_balance' => self::getTotalPoolBalance(),
            'health_status' => self::getHealthStatus(),
        ], $context));*/
    }

    /**
     * Validate payout before execution
     * Returns the safe payout amount (may be less than requested)
     */
    public static function validateAndCapPayout(int $requestedPayout): int
    {
        $totalBalance = self::getTotalPoolBalance();
        $negativeLimit = self::getNegativeLimit();
        $effectiveBalance = $totalBalance + $negativeLimit;

        // Hard stop: cannot go below negative limit
        if ($requestedPayout > $effectiveBalance) {
            self::logCriticalEvent('PAYOUT_REJECTED_EXCEEDS_LIMIT', [
                'requested' => $requestedPayout,
                'effective_balance' => $effectiveBalance,
            ]);
            return 0;
        }

        // Soft cap: reduce probability if pool is low
        $maxSafe = self::getMaxSafePayoutAmount();
        if ($requestedPayout > $maxSafe) {
            self::logCriticalEvent('PAYOUT_CAPPED_FOR_SAFETY', [
                'requested' => $requestedPayout,
                'capped_to' => $maxSafe,
                'pool_balance' => $totalBalance,
            ]);
            return $maxSafe;
        }

        return $requestedPayout;
    }

    /**
     * Get probability reduction factor based on pool health
     * Returns a multiplier (0.0 to 1.0) to apply to win probability
     */
    public static function getProbabilityReductionFactor(): float
    {
        $totalBalance = self::getTotalPoolBalance();
        $minSafe = self::getMinimumSafeBalance();
        $critical = self::getCriticalThreshold();

        // If balance is above minimum safe, no reduction
        if ($totalBalance >= $minSafe) {
            return 1.0;
        }

        // If balance is below critical, severe reduction
        if ($totalBalance < $critical) {
            // At critical threshold: 50% reduction
            // Below critical: up to 10% probability
            $ratio = max(0, $totalBalance / $critical);
            return 0.1 + ($ratio * 0.4); // 0.1 to 0.5
        }

        // Between critical and minimum safe: gradual reduction
        $range = $minSafe - $critical;
        $position = $totalBalance - $critical;
        $ratio = $range > 0 ? $position / $range : 0;

        return 0.5 + ($ratio * 0.5); // 0.5 to 1.0
    }

    /**
     * Check if a user is exploiting the system (intentional losses)
     * Returns true if user shows suspicious pattern
     */
    public static function isUserExploiting(int $userId, float $totalSpent, float $totalReceived): bool
    {
        // Pattern 1: User has spent significant amount but received almost nothing
        // then suddenly starts winning big
        if ($totalSpent > 100000 && $totalReceived < ($totalSpent * 0.3)) {
            // User is in deficit, check if they're about to exploit
            return true;
        }

        // Pattern 2: User has extreme RTP variance (too good or too bad)
        $rtp = $totalSpent > 0 ? $totalReceived / $totalSpent : 0;
        if ($rtp > 2.0 || $rtp < 0.2) {
            return true;
        }

        return false;
    }
}
