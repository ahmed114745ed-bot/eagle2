<?php


namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;

class DeviationCalculator
{
    private const APP_PROFIT_RATE = 0.10;      // 10% App Fee
    private const JACKPOT_WALLET_RATE = 0.10;  // 10% Placeholder/Misc
    private const MEDIUM_WALLET_RATE = 0.00;   // Not needed for net calculation

    /**
     * Calculate current deviation from target loss rate.
     * 
     * Negative deviation: User lost MORE than target (deficit) -> increase win chance
     * Positive deviation: User lost LESS than target (surplus) -> decrease win chance
     */
    public function calculate(float $totalBets, float $totalProfit): float
    {
        if ($totalBets <= 0) {
            return 0;
        }

        $totalDeductionRate = self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE;

        $netTotalBets = $totalBets * (1 - $totalDeductionRate);

        $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);

        $actualLoss = -$totalProfit;

        $targetLoss = $netTotalBets * $targetLossRate;

        return ($targetLoss - $actualLoss) / $netTotalBets;
    }


    public static function getDeductionRates(): array
    {
        return [
            'app_profit_rate' => self::APP_PROFIT_RATE,
            'jackpot_wallet_rate' => self::JACKPOT_WALLET_RATE,
            'medium_wallet_rate' => self::MEDIUM_WALLET_RATE,
            'total_deduction_rate' => self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE,
            'net_gift_rate' => 1 - (self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE)
        ];
    }


    public static function calculateJackpotProbability(
        float $baseJackpotProbability,
        float $betAmount,
        int $jackpotWalletBalance,
        int $globalVaultBalance,
        int $desiredMultiplier = 250
    ): float {
        $requiredPayout = max(0, ($desiredMultiplier - 1) * $betAmount);
        $totalAvailable = $jackpotWalletBalance + $globalVaultBalance;

        if ($totalAvailable <= 0 || $requiredPayout <= 0) {
            return 0;
        }

        $coverageRatio = min(1.0, $totalAvailable / $requiredPayout);

        return $baseJackpotProbability * $coverageRatio;
    }
    public static function calculateNetGiftValue(float $betAmount): float
    {
        $totalDeductionRate = self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE;
        return $betAmount * (1 - $totalDeductionRate);
    }
}
