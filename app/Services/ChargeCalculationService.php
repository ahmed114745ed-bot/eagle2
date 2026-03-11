<?php

namespace App\Services;

class ChargeCalculationService
{
    /**
     * @param float $amount The amount provided (either in USD or Coins)
     * @param string $unit 'usd' or 'coins'
     * @param float $effectiveRate The exchange rate to apply (e.g., admin or user specific)
     * @param float|null $baseRate The system base rate (defaults to effectiveRate if null)
     * @return array
     * @throws \Exception
     */
    public static function calculate(float $amount, string $unit, float $effectiveRate, ?float $baseRate = null): array
    {
        $effectiveRate = (float) $effectiveRate;
        $baseRate = $baseRate !== null ? (float) $baseRate : $effectiveRate;

        // Validation & Edge Cases
        if ($amount <= 0) {
            throw new \Exception(__('Amount must be greater than zero.'));
        }
        if ($effectiveRate <= 0) {
            throw new \Exception(__('Exchange rate must be greater than zero.'));
        }
        if ($baseRate <= 0) {
            throw new \Exception(__('Base rate must be greater than zero.'));
        }

        if ($unit === 'usd') {
            $baseUsd = round($amount, 2);
            $totalCoins = floor($amount * $effectiveRate);
            $baseCoins = floor($amount * $baseRate);
        } else {
            $totalCoins = floor($amount);
            $baseUsd = round($totalCoins / $effectiveRate, 2);
            $baseCoins = floor($baseUsd * $baseRate);
        }

        // 1 USD = 1 USD (Profit and Base are rooted in USD for accuracy)
        $profitUsd = $baseUsd;
        $profitCoins = $baseCoins;
        
        // Bonus is what the user gets extra ABOVE the base rate
        $bonusCoins = max(0, $totalCoins - $baseCoins);

        return [
            'base_usd'          => (float) $baseUsd,
            'total_coins'       => (float) $totalCoins,
            'applied_coin_rate' => $effectiveRate,
            'base_coins'        => (float) $baseCoins,
            'bonus_coins'       => (float) $bonusCoins,
            'profit_usd'        => (float) $profitUsd,
            'profit_coins'      => (float) $profitCoins,
        ];
    }
}
