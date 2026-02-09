<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;

class DeviationCalculator
{
    /**
     * Calculate current deviation from target loss rate.
     * 
     * Formula: (Actual Loss - Target Loss) / Total Bets
     * 
     * Positive deviation: User lost MORE than target (good for user) -> increase win chance
     * Negative deviation: User lost LESS than target (bad for user, user is winning) -> decrease win chance
     */
    public function calculate(float $totalBets, float $totalProfit): float
    {
        if ($totalBets <= 0) {
            return 0;
        }

        $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
        
        $actualLoss = -$totalProfit; // Profit is negative for loss
        $targetLoss = $totalBets * $targetLossRate;

        return ($actualLoss - $targetLoss) / $totalBets;
    }
}
