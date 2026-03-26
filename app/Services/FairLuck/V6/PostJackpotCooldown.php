<?php

namespace App\Services\FairLuck\V6;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * PostJackpotCooldown: Prevents consecutive big jackpots
 * 
 * Purpose:
 * - Prevents "explosive luck" that drains the pool rapidly
 * - Protects against consecutive 500x, 1000x wins
 * - Uses bet count instead of time (more fair to players)
 * 
 * Strategy:
 * - After a big jackpot (250x+), require 200 bets before next big jackpot
 * - This gives the pool time to recover from the payout
 * - Players can still win small/medium prizes during cooldown
 */
class PostJackpotCooldown
{
    private const REDIS_KEY_PREFIX = 'fairluck:v6:lastjackpot:';
    private const COOLDOWN_BET_COUNT = 200;
    private const JACKPOT_THRESHOLD = 250; // Only track 250x and above

    /**
     * Get the required cooldown in bets
     */
    public static function getCooldownBetCount(): int
    {
        return (int) \App\Models\FairLuckSetting::getByKey('fairluck_jackpot_cooldown_bets', self::COOLDOWN_BET_COUNT);
    }

    /**
     * Record a jackpot win
     */
    public static function recordJackpot(int $userId, int $currentBetCount, int $multiplier): void
    {
        if ($multiplier < self::JACKPOT_THRESHOLD) {
            return; // Only track big jackpots
        }

        $key = self::REDIS_KEY_PREFIX . $userId;
        Redis::set($key, $currentBetCount);
        Redis::expire($key, 86400 * 30); // Keep for 30 days

        Log::info("JACKPOT_COOLDOWN: Recorded jackpot for user {$userId}", [
            'multiplier' => $multiplier,
            'bet_count' => $currentBetCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get the last jackpot bet count for a user
     */
    public static function getLastJackpotBetCount(int $userId): ?int
    {
        $key = self::REDIS_KEY_PREFIX . $userId;
        $value = Redis::get($key);

        return $value ? (int) $value : null;
    }

    /**
     * Check if user is in cooldown period
     */
    public static function isInCooldown(int $userId, int $currentBetCount): bool
    {
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

        Log::info("JACKPOT_COOLDOWN: Cooldown cleared for user {$userId}");
    }

    /**
     * Get cooldown statistics
     */
    public static function getStatistics(): array
    {
        $pattern = self::REDIS_KEY_PREFIX . '*';
        $keys = Redis::keys($pattern);

        $stats = [
            'total_users_with_cooldown' => count($keys),
            'cooldown_threshold' => self::getCooldownBetCount(),
            'timestamp' => now()->toIso8601String(),
        ];

        return $stats;
    }
}
