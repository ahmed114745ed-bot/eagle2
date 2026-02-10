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
        $adjustmentFactor = (float) FairLuckSetting::getByKey('adjustment_factor', 0.5);

        // Adjust probability based on deviation
        // If deviation is negative (user lost more), increase probability
        // If deviation is positive (user won more), decrease probability
        $adjustedProb = $baseProb - ($deviation * $adjustmentFactor);

        // Apply beginner boost
        $finalProb = $adjustedProb * $protectionMultiplier;

        // Clamp values between 0.5% and 95%
        return max(0.005, min(0.95, $finalProb));
    }
}
