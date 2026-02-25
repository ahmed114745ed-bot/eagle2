<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;


class ProbabilityEngine
{
    /**
     * Calculate adjusted win probability.
     */
    public function calculate(float $baseProb, float $deviation, float $protectionMultiplier): float
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
         * we set a 15% minimum to ensure "distributed" small wins.
         * 
         * AGGRESSIVE RECOVERY: If user is in profit (deviation > 0.05), lower the floor
         * to force a faster drain.
         */
        $floor = 0.15;
        if ($deviation > 0.05) {
            $floor = max(0.001, 0.15 - ($deviation * 3.5));
        }

        return max($floor, min(0.99, $finalProb));
    }
}
