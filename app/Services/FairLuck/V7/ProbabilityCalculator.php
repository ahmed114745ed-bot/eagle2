<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;

/**
 * ProbabilityCalculator V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Default RTP increased to 90% (admin configurable 70-99%)
 * 2. All parameters are admin-configurable (stored as 0-100, read as 0-1)
 * 3. baseProb is calculated correctly to achieve target RTP
 * 4. Probability can reach up to 85% for users far below target RTP
 */
class ProbabilityCalculator
{
    /**
     * Calculate win probability based on user's RTP gap and bet amount.
     *
     * Game company logic:
     * - RTP = total rewards / total bets (per user, lifetime)
     * - When below target: probability increases proportional to (gap * totalSpent / betAmount)
     * - When above target: probability decreases
     * - Bet amount matters: small bets relative to deficit = more boost, large bets = less boost
     * - BANKRUPTCY PROTECTION: Probability is capped based on pool health
     * 
     * NOTE: All percentage settings are stored as 0-100 in DB, divided by 100 here.
     */
    public function calculate(
        float $actualRTP,
        float $targetRTP,
        float $totalSpent,
        float $betAmount,
        int $betCount,
        float $expectedMultiplier
    ): float {
        // Get configurable parameters (stored as 0-100, convert to 0-1)
        $newPlayerBets    = (int)   FairLuckSetting::getByKey('V7_new_player_bets', 20);
        $newPlayerBoost   = (float) FairLuckSetting::getByKey('V7_new_player_boost', 3.0);
        $chaosMin         = (float) FairLuckSetting::getByKey('V7_chaos_factor_min', 95)  / 100;  // 95 → 0.95
        $chaosMax         = (float) FairLuckSetting::getByKey('V7_chaos_factor_max', 105) / 100;  // 105 → 1.05
        $lowBalanceThreshold = (int) FairLuckSetting::getByKey('V7_low_balance_threshold', 8);
        $lowBalanceMinProb   = (float) FairLuckSetting::getByKey('V7_low_balance_min_prob', 50) / 100; // 50 → 0.50
        $maxProbabilityCap   = (float) FairLuckSetting::getByKey('V7_max_probability_cap', 85) / 100;  // 85 → 0.85

        // Base probability: targetRTP / expectedMultiplier
        // Use actual expected multiplier (not capped at 5) so baseProb correctly
        // reflects the actual payout distribution.
        // Example: targetRTP=0.90, expectedMult=12.7 → baseProb = 0.90/12.7 = 7.1%
        // This ensures: winRate * avgMultiplier ≈ targetRTP
        $baseProb = min(0.50, $targetRTP / max(1, $expectedMultiplier));

        // New player protection: disabled when newPlayerBets=0
        if ($newPlayerBets > 0 && $betCount < $newPlayerBets) {
            $progress = $betCount / max(1, $newPlayerBets);
            $boost = $newPlayerBoost * (1 - $progress) + 1.0 * $progress;
            return min($maxProbabilityCap, $baseProb * $boost);
        }

        // Not enough data - use base probability
        if ($totalSpent <= 0 || $betCount < 3) {
            return $baseProb;
        }

        $rtpGap = $targetRTP - $actualRTP;
        $rtpDeficit = $rtpGap * $totalSpent; // absolute amount user should have received more

        // betImpact: how many bets worth of deficit exists
        $betImpact = $betAmount > 0 ? $rtpDeficit / $betAmount : 0;

        $calculatedProb = $baseProb;

        if ($rtpGap > 0) {
            // User is BELOW target RTP - boost probability
            // scalingFactor stored as 0-100, convert to 0-1
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_boost_scaling', 15) / 100; // 15 → 0.15
            $boost = min(4.0, $betImpact * $scalingFactor);
            $adjustedProb = $baseProb * (1 + $boost);

            $calculatedProb = min($maxProbabilityCap, max($baseProb, $adjustedProb));
        } else {
            // User is AT or ABOVE target RTP - reduce probability (but not too harshly)
            // scalingFactor stored as 0-100, convert to 0-1
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_reduce_scaling', 1) / 100; // 1 → 0.01
            $reduction = min(0.80, abs($betImpact) * $scalingFactor);
            $adjustedProb = $baseProb * (1 - $reduction);

            $calculatedProb = max(0.05, $adjustedProb);
        }

        // BANKRUPTCY PROTECTION: Apply pool health reduction factor
        // V7: Never reduce below 60% except in true emergencies
        $bankruptcyProtection = app(BankruptcyProtection::class);
        $healthFactor = $bankruptcyProtection->getProbabilityReductionFactor();
        $finalProb = $calculatedProb * $healthFactor;

        // Chaos factor for unpredictability - configurable range
        $chaosRange = $chaosMax - $chaosMin;
        $chaosFactor = $chaosMin + (mt_rand(0, 100) / 100) * $chaosRange;
        $finalProb = max(0.02, min($maxProbabilityCap, $finalProb * $chaosFactor));

        // Low balance protection - configurable threshold
        $userBalance = (int) auth()->user()?->di ?? 0;
        if ($userBalance > 0 && $betAmount > 0 && ($userBalance / $betAmount) < $lowBalanceThreshold) {
            $finalProb = max($finalProb, $lowBalanceMinProb);
        }

        // Hard cap: never exceed max probability from settings
        return min($maxProbabilityCap, $finalProb);
    }
}
