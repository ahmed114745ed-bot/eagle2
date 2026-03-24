<?php

namespace App\Services\FairLuck\V6;

class RewardSelector
{
    /**
     * Select multiplier based on RTP gap.
     *
     * Game company logic:
     * - All rewards share one prize pool
     * - Based on user's current P&L: if loss is significant, probability of ALL prizes increases
     * - But the extent of increase varies per tier (higher tiers get more boost when deficit is large)
     */
    public function select(
        float $rtpGap,
        float $betAmount,
        int $totalPoolBalance,
        int $betCount
    ): int {
        $multipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        $weights = [];

        foreach ($multipliers as $m) {
            $baseWeight = $this->getBaseWeight($m);
            $payout = $m * $betAmount;

            // Pool solvency check - graduated by tier
            // Higher multiplier = more relaxed (jackpots are rare, pool recovers)
            // solvencyLimit: higher = more relaxed (pool needs less % of payout)
            $solvencyLimit = match(true) {
                $m >= 500  => 2.50,  // pool needs > 40% of payout
                $m >= 250  => 2.00,  // pool needs > 50% of payout
                $m >= 100  => 1.50,  // pool needs > 67% of payout
                default    => 0.95,  // pool needs > 105% of payout (strict)
            };
            if ($payout > $totalPoolBalance * $solvencyLimit) {
                $weights[] = 0;
                continue;
            }

            $weight = $this->adjustWeight($m, $baseWeight, $rtpGap, $betCount);
            $weights[] = max(0, (int) round($weight));
        }

        if (array_sum($weights) <= 0) {
            $weights[0] = 1;
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function adjustWeight(int $multiplier, int $baseWeight, float $rtpGap, int $betCount): float
    {
        // Guard: prevent early jackpots that destabilize user RTP
        // A 1000x win at bet #25 creates RTP ~50.0, taking hundreds of bets to normalize
        if ($betCount < 30 && $multiplier > 100) return 0;
        if ($betCount < 100 && $multiplier > 500) return 0;

        if ($rtpGap > 0.15) {
            // Significant deficit - boost ALL tiers, especially medium/high
            $gapBoost = min(8.0, $rtpGap * 30);

            if ($multiplier >= 250) {
                return $baseWeight * $gapBoost * 4.0;
            } elseif ($multiplier >= 50) {
                return $baseWeight * $gapBoost * 2.5;
            } else {
                return $baseWeight * $gapBoost * 1.5;
            }
        }

        if ($rtpGap > 0.05) {
            // Moderate deficit
            $gapBoost = 1 + $rtpGap * 12;

            if ($multiplier >= 250) {
                return $baseWeight * $gapBoost * 2.0;
            } elseif ($multiplier >= 50) {
                return $baseWeight * $gapBoost * 1.8;
            } else {
                return $baseWeight * $gapBoost;
            }
        }

        if ($rtpGap > 0) {
            // Slight deficit - mild boost
            $gapBoost = 1 + $rtpGap * 5;

            if ($multiplier >= 250) {
                return $baseWeight * $gapBoost;
            } else {
                return $baseWeight * $gapBoost;
            }
        }

        if ($rtpGap > -0.05) {
            // Near target or slightly above - still allow small jackpot chance
            if ($multiplier >= 500) return max(1, (int) ($baseWeight * 0.08));
            if ($multiplier >= 250) return max(2, (int) ($baseWeight * 0.15));
            if ($multiplier >= 50) return $baseWeight * 0.3;
            return $baseWeight;
        }

        // Well above target - reduce but don't fully block jackpots
        if ($multiplier >= 500) return 0;
        if ($multiplier >= 250) return max(1, (int) ($baseWeight * 0.05));
        if ($multiplier > 20) return $baseWeight * 0.1;
        if ($multiplier > 5) return $baseWeight * 0.3;
        return $baseWeight * 0.5;
    }

    public function getBaseWeight(int $multiplier): int
    {
        // Higher medium-tier weights than V3 so users feel real wins more often
        return match ($multiplier) {
            5 => 2500,
            10 => 1800,
            20 => 1200,
            50 => 800,
            70 => 500,
            100 => 300,
            250 => 150,
            500 => 40,
            1000 => 10,
            default => 1,
        };
    }

    public function getExpectedMultiplier(): float
    {
        $multipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        $totalWeight = 0;
        $totalValue = 0;

        foreach ($multipliers as $m) {
            $weight = $this->getBaseWeight($m);
            $totalWeight += $weight;
            $totalValue += ($m * $weight);
        }

        return $totalWeight > 0 ? ($totalValue / $totalWeight) : 10.0;
    }

    /**
     * Validate pool can afford the payout. If not, fall back to lower multiplier.
     * NEVER cancels a win - always finds the highest affordable multiplier.
     */
    public function validateAndFallback(int $selectedMultiplier, float $betAmount, int $totalPoolBalance): int
    {
        $allMultipliers = [1000, 500, 250, 100, 70, 50, 20, 10, 5];
        $negativeLimit = \App\Models\FairLuckSetting::getNegativeLimit();
        $effectiveBalance = $totalPoolBalance + $negativeLimit;

        foreach ($allMultipliers as $m) {
            if ($m > $selectedMultiplier) continue;

            $payout = $m * $betAmount;
            if ($effectiveBalance >= $payout) {
                return $m;
            }
        }

        return 0;
    }

    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) return $values[0];

        $random = mt_rand(1, (int) $totalWeight);
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
