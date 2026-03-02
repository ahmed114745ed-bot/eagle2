<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;


class ProbabilityEngine
{
    /**
     * Calculate adjusted win probability.
     */
    public function calculate(float $baseProb, float $deviation, float $protectionMultiplier, float $userBalance = 0, float $betAmount = 0): float
    {
        // Extreme adjustment factor for absolute stability (Stay at 99.5% RTP)
        $adjustmentFactor = 2.5;

        // Immediate Safety Net Logic:
        // If user is even 0.5% below target, push them back up aggressively
        if ($deviation < -0.005) {
            $adjustmentFactor = 4.0;
        }

        // Adjust probability based on deviation
        $adjustedProb = $baseProb - ($deviation * $adjustmentFactor);

        // Apply beginner boost
        $finalProb = $adjustedProb * $protectionMultiplier;

        /**
         * Clamping & Loss Distribution
         * Since the minimum multiplier is now 5x, the floor must be lower than 20%
         * to allow the house to regain balance.
         * 
         * Recommended Floor: 10% (0.10 * 5x = 0.50 RTP during "bad" luck)
         */
        $floor = 0.10;

        /**
         * LOW BALANCE PROTECTION:
         * Boost to 15% to keep them in the loop without making it too profitable.
         */
        if ($userBalance > 0 && $betAmount > 0) {
            $hitsLeft = $userBalance / $betAmount;
            if ($hitsLeft < 10) {
                $floor = 0.15;
            }
        }

        if ($deviation > 0.05) {
            $floor = max(0.05, $floor - ($deviation * 1.0));
        }

        return max($floor, min(0.99, $finalProb));
    }
}
