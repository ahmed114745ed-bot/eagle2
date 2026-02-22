<?php

namespace App\Services\FairLuck;

/**
 * FairLuckServiceAlshamla boosts mid-tier multipliers (50x, 70x, 100x)
 * while remaining fully compatible with the Service 3 flow.
 */
class FairLuckServiceAlshamla extends FairLuckService3
{
    protected function getAvailableMultipliers(): array
    {
        return [5, 10, 20, 50, 70, 100, 250, 500, 1000];
    }

    protected function getNaturalWeight(int $multiplier): int
    {
        if ($multiplier === 70) {
            return 22;
        }

        $base = parent::getNaturalWeight($multiplier);

        if (in_array($multiplier, [50, 100], true)) {
            return (int) max(1, round($base * 1.15));
        }

        return $base;
    }

    protected function neutralSmallWinWeight(
        int $multiplier,
        float $baseWeight,
        float $chaos,
        int $streak,
        bool $isBeginner
    ): float {
        $weight = parent::neutralSmallWinWeight($multiplier, $baseWeight, $chaos, $streak, $isBeginner);

        if ($multiplier === 50) {
            return $weight * 1.35;
        }

        if ($multiplier === 70) {
            $streakBoost = 1 + min(0.5, $streak * 0.05);
            $beginnerBoost = $isBeginner ? 1.3 : 1.0;

            return max(
                $weight,
                $baseWeight * 0.9 * $chaos * $streakBoost * $beginnerBoost
            );
        }

        return $weight;
    }

    protected function cautiousDistributionWeight(int $multiplier, float $baseWeight, int $streak): float
    {
        $weight = parent::cautiousDistributionWeight($multiplier, $baseWeight, $streak);

        if ($multiplier === 50) {
            return $weight * 1.2;
        }

        if ($multiplier === 70) {
            $streakPenalty = max(0.4, 1 - min(0.6, $streak * 0.04));

            return max($weight, $baseWeight * 0.55 * $streakPenalty);
        }

        return $weight;
    }

    protected function ratingSensitiveWeight(
        int $multiplier,
        float $baseWeight,
        float $deviation,
        float $chaos,
        int $streak,
        float $jackpotScaling,
        int $pityCount,
        bool $isDrainLocked,
        bool $isBeginner,
        bool $isTopLossCandidate = false,
        array $highTierAvailability = [],
        float $poolWeight = 1.0
    ): float {
        $weight = parent::ratingSensitiveWeight(
            $multiplier,
            $baseWeight,
            $deviation,
            $chaos,
            $streak,
            $jackpotScaling,
            $pityCount,
            $isDrainLocked,
            $isBeginner,
            $isTopLossCandidate,
            $highTierAvailability,
            $poolWeight
        );

        if ($multiplier === 100 && !$isDrainLocked) {
            $weight *= 1.3;
            if ($pityCount > 450) {
                $weight *= 1.1;
            }
        }

        return $weight;
    }

    protected function tweakMultiplierWeights(array $multipliers, array $weights, array $context): array
    {
        if (!empty($context['force_jackpot']) || !empty($context['force_mini_wins']) || !empty($context['is_drain_locked'])) {
            return $weights;
        }

        foreach ($multipliers as $index => $multiplier) {
            if (!array_key_exists($index, $weights)) {
                continue;
            }

            $boost = match ($multiplier) {
                50 => 1.25,
                70 => 1.6,
                100 => 1.2,
                default => null,
            };

            if ($boost === null) {
                continue;
            }

            $weights[$index] = (int) max(1, round(max(0, $weights[$index]) * $boost));

            if ($multiplier === 70 && $weights[$index] <= 0) {
                $weights[$index] = (int) max(1, round($this->getNaturalWeight(70) * 0.75));
            }
        }

        return $weights;
    }
}
