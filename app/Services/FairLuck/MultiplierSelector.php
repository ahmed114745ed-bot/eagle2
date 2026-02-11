<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;

class MultiplierSelector
{
    /**
     * Select a win multiplier based on deviation.
     */
    public function select(float $deviation): int
    {
        $availableMultipliers = FairLuckSetting::getByKey('available_multipliers', [5, 10, 20, 50, 100, 250, 500, 1000]);
        
        // Dynamic weights based on deviation
        $weights = $this->calculateWeights($availableMultipliers, $deviation);

        return $this->weightedRandom($availableMultipliers, $weights);
    }

    private function calculateWeights(array $multipliers, float $deviation): array
    {
        $weights = [];
        
        foreach ($multipliers as $m) {
            if ($deviation < -0.15) { // User in significant deficit (Lost > 15% more than target)
                // Reward user: favor high multipliers but be less aggressive with jackpots
                if ($m >= 500) {
                    $weights[] = 5; // Moderate weight for jackpots
                } else {
                    $weights[] = $m >= 100 ? 50 : 10;
                }
            } elseif ($deviation > 0.01) { // User is winning or in surplus
                // BE VERY STRICT
                // Absolutely no multipliers above 20x if user is even slightly in surplus
                if ($m > 20) {
                    $weights[] = 0;
                } else {
                    // Favor the smallest multiplier (5x) heavily
                    $weights[] = $m == 5 ? 1000 : 1;
                }
            } else { // Normal balanced distribution (-0.15 < dev < 0.01)
                $weight = $this->getNormalWeight($m);
                // In normal state, only allow up to 100x.
                // 250x, 500x and 1000x are reserved ONLY for users in significant deficit.
                if ($m >= 250) {
                    $weights[] = 0; 
                } else {
                    $weights[] = $weight;
                }
            }
        }

        return $weights;
    }

    private function getNormalWeight(int $multiplier): int
    {
        // Extreme weights for small multipliers to maximize playtime (Super Low Volatility)
        return match ($multiplier) {
            5 => 2000,  // Was 800 - Heavily favor the lowest win to keep balance stable
            10 => 800,  // Was 400
            20 => 200,  // Was 150
            50 => 20,   // Was 40
            100 => 5,   // Was 10
            250 => 1,   // Was 5
            500 => 0,   // Reserved for deficit only
            1000 => 0,  // Reserved for deficit only
            default => 1,
        };
    }

    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) {
            return $values[0];
        }
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }

        return $values[0];
    }

    /**
     * Calculate the expected multiplier value based on normal weights.
     * This is used to determine the ideal base win probability.
     */
    public function getExpectedMultiplier(): float
    {
        $availableMultipliers = FairLuckSetting::getByKey('available_multipliers', [5, 10, 20, 50, 100, 250, 500, 1000]);
        $totalWeight = 0;
        $totalValue = 0;

        foreach ($availableMultipliers as $m) {
            $weight = $this->getNormalWeight($m);
            $totalWeight += $weight;
            $totalValue += ($m * $weight);
        }

        return $totalWeight > 0 ? ($totalValue / $totalWeight) : 10.0;
    }
}
