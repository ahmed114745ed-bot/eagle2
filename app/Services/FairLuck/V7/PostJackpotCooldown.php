<?php

namespace App\Services\FairLuck\V7;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * PostJackpotCooldown V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Cooldown is DISABLED by default (0 bets) - users can win big back-to-back
 * 2. Admin can re-enable cooldown by setting a value from the panel
 * 3. Bet count gates (30 for 100x+, 100 for 500x+) are now configurable
 */
class PostJackpotCooldown
{
    private const REDIS_KEY_PREFIX = 'fairluck:V7:lastjackpot:';
    private const JACKPOT_THRESHOLD = 250; // Only track 250x and above

    /**
     * Get the required cooldown in bets (0 = disabled)
     */
    public static function getCooldownBetCount(): int
    {
        // Default is 0 (disabled) - admins can enable by setting a value
        return (int) \App\Models\FairLuckSetting::getByKey('fairluck_jackpot_cooldown_bets', 0);
    }

    /**
     * Check if cooldown is enabled
     */
    public static function isEnabled(): bool
    {
        return self::getCooldownBetCount() > 0;
    }

    /**
     * Record a jackpot win
     */
    public static function recordJackpot(int $userId, int $currentBetCount, int $multiplier): void
    {
        // Only track if cooldown is enabled and it's a big jackpot
        if (!self::isEnabled() || $multiplier < self::JACKPOT_THRESHOLD) {
            return;
        }

        $key = self::REDIS_KEY_PREFIX . $userId;
        Redis::set($key, $currentBetCount);
        Redis::expire($key, 86400 * 30); // Keep for 30 days

    }

    /**
     * Get the last jackpot bet count for a user
     */
    public static function getLastJackpotBetCount(int $userId): ?int
    {
        if (!self::isEnabled()) {
            return null;
        }

        $key = self::REDIS_KEY_PREFIX . $userId;
        $value = Redis::get($key);

        return $value ? (int) $value : null;
    }

    /**
     * Check if user is in cooldown period
     */
    public static function isInCooldown(int $userId, int $currentBetCount): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $lastJackpotBet = self::getLastJackpotBetCount($userId);

        if ($lastJackpotBet === null) {
            return false; // No previous jackpot
        }

        $betsSinceJackpot = $currentBetCount - $lastJackpotBet;
        $cooldownBets = self::getCooldownBetCount();

        return $betsSinceJackpot < $cooldownBets;
    }

    /**
     * Get bets remaining in cooldown
     */
    public static function getBetsRemaining(int $userId, int $currentBetCount): int
    {
        if (!self::isEnabled()) {
            return 0;
        }

        $lastJackpotBet = self::getLastJackpotBetCount($userId);

        if ($lastJackpotBet === null) {
            return 0; // No cooldown
        }

        $betsSinceJackpot = $currentBetCount - $lastJackpotBet;
        $cooldownBets = self::getCooldownBetCount();

        return max(0, $cooldownBets - $betsSinceJackpot);
    }

    /**
     * Get cooldown status for a user
     */
    public static function getCooldownStatus(int $userId, int $currentBetCount): array
    {
        $lastJackpotBet = self::getLastJackpotBetCount($userId);
        $isInCooldown = self::isInCooldown($userId, $currentBetCount);
        $betsRemaining = self::getBetsRemaining($userId, $currentBetCount);

        return [
            'user_id' => $userId,
            'last_jackpot_bet' => $lastJackpotBet,
            'current_bet_count' => $currentBetCount,
            'is_in_cooldown' => $isInCooldown,
            'bets_remaining' => $betsRemaining,
            'cooldown_enabled' => self::isEnabled(),
            'cooldown_threshold' => self::getCooldownBetCount(),
            'status' => $isInCooldown ? 'cooling_down' : 'ready',
        ];
    }

    /**
     * Apply cooldown restriction to multiplier selection
     * Returns 0 if big jackpot is blocked, otherwise returns original multiplier
     */
    public static function applyRestriction(int $userId, int $currentBetCount, int $selectedMultiplier): int
    {
        // If cooldown is disabled, always allow
        if (!self::isEnabled()) {
            return $selectedMultiplier;
        }

        // Only restrict big jackpots (250x and above)
        if ($selectedMultiplier < self::JACKPOT_THRESHOLD) {
            return $selectedMultiplier;
        }

        // Check if in cooldown
        if (self::isInCooldown($userId, $currentBetCount)) {
            $betsRemaining = self::getBetsRemaining($userId, $currentBetCount);

            Log::warning("JACKPOT_COOLDOWN: Big jackpot blocked for user {$userId}", [
                'multiplier' => $selectedMultiplier,
                'bets_remaining' => $betsRemaining,
                'current_bet_count' => $currentBetCount,
            ]);

            return 0; // Block the big jackpot
        }

        return $selectedMultiplier;
    }

    /**
     * Clear cooldown for a user (admin function)
     */
    public static function clearCooldown(int $userId): void
    {
        $key = self::REDIS_KEY_PREFIX . $userId;
        Redis::del($key);

    }

    /**
     * Get cooldown statistics
     */
    public static function getStatistics(): array
    {
        if (!self::isEnabled()) {
            return [
                'cooldown_enabled' => false,
                'total_users_with_cooldown' => 0,
                'timestamp' => now()->toIso8601String(),
            ];
        }

        $pattern = self::REDIS_KEY_PREFIX . '*';
        $keys = Redis::keys($pattern);

        $stats = [
            'cooldown_enabled' => true,
            'total_users_with_cooldown' => count($keys),
            'cooldown_threshold' => self::getCooldownBetCount(),
            'timestamp' => now()->toIso8601String(),
        ];

        return $stats;
    }
}
