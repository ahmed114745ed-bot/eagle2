<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;

/**
 * ProbabilityCalculator V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Default RTP increased to 92% (admin configurable 70-99%)
 * 2. All parameters are admin-configurable
 * 3. Probability never goes below 60% of normal (except in extreme emergencies)
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
     */
    public function calculate(
        float $actualRTP,
        float $targetRTP,
        float $totalSpent,
        float $betAmount,
        int $betCount,
        float $expectedMultiplier
    ): float {
        // Get configurable parameters
        $newPlayerBets = (int) FairLuckSetting::getByKey('V7_new_player_bets', 20);
        $newPlayerBoost = (float) FairLuckSetting::getByKey('V7_new_player_boost', 3.0);
        $chaosMin = (float) FairLuckSetting::getByKey('V7_chaos_factor_min', 0.90);
        $chaosMax = (float) FairLuckSetting::getByKey('V7_chaos_factor_max', 1.10);
        $lowBalanceThreshold = (int) FairLuckSetting::getByKey('V7_low_balance_threshold', 15);
        $lowBalanceMinProb = (float) FairLuckSetting::getByKey('V7_low_balance_min_prob', 0.18);

        // Base probability: targetRTP / expectedMultiplier
        // Cap expectedMultiplier at 20 to avoid baseProb becoming near-zero.
        // The actual multiplier distribution is handled by RewardSelector weights.
        // Without this cap: E[mult] ≈ 148 → baseProb ≈ 0.006 (too low!)
        // With cap at 20: baseProb = 0.92 / 20 = 0.046 (reasonable starting point)
        $cappedExpectedMultiplier = min($expectedMultiplier, 20.0);
        $baseProb = min(0.35, $targetRTP / max(1, $cappedExpectedMultiplier));

        // New player protection (one-time, gradual transition)
        if ($betCount < $newPlayerBets) {
            $progress = $betCount / max(1, $newPlayerBets);
            $boost = $newPlayerBoost * (1 - $progress) + 1.0 * $progress;
            return min(0.50, $baseProb * $boost);
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
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_boost_scaling', 0.05);
            $boost = min(4.0, $betImpact * $scalingFactor);
            $adjustedProb = $baseProb * (1 + $boost);

            $calculatedProb = min(0.85, max($baseProb, $adjustedProb));
        } else {
            // User is AT or ABOVE target RTP - reduce probability (but not too harshly)
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_reduce_scaling', 0.02);
            $reduction = min(0.80, abs($betImpact) * $scalingFactor);
            $adjustedProb = $baseProb * (1 - $reduction);

            $calculatedProb = max(0.05, $adjustedProb);
        }

        // BANKRUPTCY PROTECTION: Apply pool health reduction factor
        // V7: Never reduce below 60% except in true emergencies
        $bankruptcyProtection = app(BankruptcyProtection::class);
        $healthFactor = $bankruptcyProtection->getProbabilityReductionFactor();
        $finalProb = $calculatedProb * $healthFactor;

        // Chaos factor (0.90 to 1.10) for unpredictability - configurable range
        $chaosRange = $chaosMax - $chaosMin;
        $chaosFactor = $chaosMin + (mt_rand(0, 100) / 100) * $chaosRange;
        $finalProb = max(0.02, min(0.90, $finalProb * $chaosFactor));

        // Low balance protection - configurable threshold
        $userBalance = (int) auth()->user()?->di ?? 0;
        if ($userBalance > 0 && $betAmount > 0 && ($userBalance / $betAmount) < $lowBalanceThreshold) {
            $finalProb = max($finalProb, $lowBalanceMinProb);
        }

        // Hard cap: never exceed max probability from settings
        $maxProbability = (float) FairLuckSetting::getByKey('V7_max_probability_cap', 0.50);
        return min($maxProbability, $finalProb);
    }
}
