<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;

class DeviationCalculator
{
    /**
     * Calculate current deviation from target loss rate.
     * 
     * Formula: (Target Loss - Actual Loss) / Total Bets
     * 
     * Negative deviation: User lost MORE than target (deficit) -> increase win chance
     * Positive deviation: User lost LESS than target (surplus) -> decrease win chance
     */
    public function calculate(float $totalBets, float $totalProfit): float
    {
        if ($totalBets <= 0) {
            return 0;
        }

        $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
        
        $actualLoss = -$totalProfit; // Profit is negative for loss
        $targetLoss = $totalBets * $targetLossRate;

        // Alignment with spec: Negative = lost more than target
        return ($targetLoss - $actualLoss) / $totalBets;
    }
}
