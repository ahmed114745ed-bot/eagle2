<?php

namespace App\Services;

class ChargeCalculationService
{
    /**
     * Calculate charge snapshot data with proper profit calculation.
     * 
     * IMPORTANT: Profit is calculated ONLY on base_coins (excluding bonus_coins)
     * as per business rules. This ensures accurate financial reporting.
     *
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

        // Bonus is what the user gets extra ABOVE the base rate
        $bonusCoins = max(0, $totalCoins - $baseCoins);

        /**
         * PROFIT CALCULATION (Business Rule):
         * - Profit is calculated ONLY on base_coins, EXCLUDING bonus_coins
         * - bonus_coins are a cost to the platform (cashback/promotion)
         * - profit_coins = base_coins (the actual value transferred at base rate)
         * - profit_usd = base_usd (the USD equivalent of base transaction)
         */
        $profitCoins = $baseCoins;
        $profitUsd = $baseUsd;

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
