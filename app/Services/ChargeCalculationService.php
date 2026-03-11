<?php

namespace App\Services;

class ChargeCalculationService
{
    /**
     * 
     * @param float $amount The amount provided (either in USD or Coins)
     * @param string $unit 'usd' or 'coins'
     * @param float $effectiveRate The exchange rate to apply for this transaction
     * @return array
     */
    public static function calculate(float $amount, string $unit, float $effectiveRate): array
    {
        $effectiveRate = (float) $effectiveRate;
        
        if ($unit === 'usd') {
            $baseUsd = $amount;
            $totalCoins = $amount * $effectiveRate;
        } else {
            $totalCoins = $amount;
            $baseUsd = $effectiveRate > 0 ? $totalCoins / $effectiveRate : 0;
        }

        $baseCoins = $baseUsd * $effectiveRate;
        $profitCoins = $baseCoins;
        $bonusCoins = $totalCoins - $baseCoins;
        $profitUsd = $baseUsd;

        return [
            'base_usd' => (float) $baseUsd,
            'total_coins' => (float) $totalCoins,
            'applied_coin_rate' => $effectiveRate,
            'base_coins' => (float) $baseCoins,
            'bonus_coins' => (float) $bonusCoins,
            'profit_usd' => (float) $profitUsd,
            'profit_coins' => (float) $profitCoins,
        ];
    }
}
