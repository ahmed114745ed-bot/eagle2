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

        // Clamp values - Allow up to 99% win chance if they are losing, to force a hit
        return max(0.01, min(0.99, $finalProb));
    }
}
