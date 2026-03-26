<?php

namespace App\Services\FairLuck\V6;

use App\Models\FairLuckSetting;

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
        $newPlayerBets = (int) FairLuckSetting::getByKey('v6_new_player_bets', 20);
        $newPlayerBoost = (float) FairLuckSetting::getByKey('v6_new_player_boost', 3.0);

        // Base probability: targetRTP / expectedMultiplier
        // This ensures long-term convergence: prob * E[multiplier] ≈ targetRTP
        $baseProb = min(0.35, $targetRTP / max(1, $expectedMultiplier));

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
        // If deficit is 500 and bet is 5000 → betImpact = 0.1 (low, one bet can cover it)
        // If deficit is 10000 and bet is 10 → betImpact = 1000 (high, needs many bets)
        $betImpact = $betAmount > 0 ? $rtpDeficit / $betAmount : 0;

        $calculatedProb = $baseProb;

        if ($rtpGap > 0) {
            // User is BELOW target RTP - boost probability
            $scalingFactor = (float) FairLuckSetting::getByKey('v6_boost_scaling', 0.05);
            $boost = min(4.0, $betImpact * $scalingFactor);
            $adjustedProb = $baseProb * (1 + $boost);

            $calculatedProb = min(0.85, max($baseProb, $adjustedProb));
        } else {
            // User is AT or ABOVE target RTP - reduce probability (but not too harshly)
            $scalingFactor = (float) FairLuckSetting::getByKey('v6_reduce_scaling', 0.02);
            $reduction = min(0.80, abs($betImpact) * $scalingFactor);
            $adjustedProb = $baseProb * (1 - $reduction);

            $calculatedProb = max(0.05, $adjustedProb);
        }

        // BANKRUPTCY PROTECTION: Apply pool health reduction factor
        $bankruptcyProtection = app(BankruptcyProtection::class);
        $healthFactor = $bankruptcyProtection->getProbabilityReductionFactor();
        $finalProb = $calculatedProb * $healthFactor;

        // Hard cap: never exceed 50% probability (prevents exploitation)
        $maxProbability = (float) FairLuckSetting::getByKey('v6_max_probability_cap', 0.50);
        return min($maxProbability, $finalProb);
    }
}
