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
            if ($deviation > 0.05) { // User lost a lot, favor high multipliers
                $weights[] = $m >= 100 ? 50 : 10;
            } elseif ($deviation < -0.05) { // User is winning, favor low multipliers
                $weights[] = $m <= 20 ? 100 : 1;
            } else { // Normal balanced distribution
                $weights[] = $this->getNormalWeight($m);
            }
        }

        return $weights;
    }

    private function getNormalWeight(int $multiplier): int
    {
        return match ($multiplier) {
            5 => 500,
            10 => 300,
            20 => 100,
            50 => 50,
            100 => 20,
            250 => 10,
            500 => 5,
            1000 => 2,
            default => 1,
        };
    }

    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
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
}
