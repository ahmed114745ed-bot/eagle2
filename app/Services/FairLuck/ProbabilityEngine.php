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
         * Instead of allowing 1% floor (which causes long losing streaks), 
         * we set a 20% minimum to ensure "distributed" small wins and constant engagement.
         * 
         * SMOOTH RECOVERY: If user is in profit (deviation > 0.05), we decay the floor
         * but keep it at a reasonable level (minimum 8%) to prevent "dead zones".
         */
        $floor = 0.20;

        /**
         * LOW BALANCE PROTECTION:
         * If the user is running low on funds (less than 10 hits available),
         * we boost the floor to 25% to keep them in the loop.
         */
        if ($userBalance > 0 && $betAmount > 0) {
            $hitsLeft = $userBalance / $betAmount;
            if ($hitsLeft < 10) {
                $floor = 0.25;
            }
        }

        if ($deviation > 0.05) {
            $floor = max(0.08, $floor - ($deviation * 1.5));
        }

        return max($floor, min(0.99, $finalProb));
    }
}
