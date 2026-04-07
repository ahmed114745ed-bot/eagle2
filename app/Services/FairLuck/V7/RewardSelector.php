<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;

/**
 * RewardSelector V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Configurable multiplier weights (admin panel tunable)
 * 2. Flattened distribution - large wins occur significantly more often
 * 3. Integrates with BankruptcyProtection for wallet-aware multiplier limits
 * 4. Respects bet count gates for high multipliers
 */
class RewardSelector
{
    /**
     * Select multiplier based on RTP gap and wallet health.
     */
    public function select(
        float $rtpGap,
        float $betAmount,
        int $totalPoolBalance,
        int $betCount
    ): int {
        $multipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        $weights = [];
        
        // Get configurable weights
        $configWeights = FairLuckSetting::getMultiplierWeights();
        
        // Get wallet-based max multiplier
        $maxMultiplier = BankruptcyProtection::getMaxMultiplierForHealth();

        foreach ($multipliers as $m) {
            // Skip if multiplier exceeds wallet health limit
            if ($m > $maxMultiplier) {
                $weights[] = 0;
                continue;
            }
            
            $payout = $m * $betAmount;

            // Pool solvency check - graduated by tier
            // Logic: payout must be <= pool * solvencyFactor
            // Small multipliers: pool must cover 100%+ of payout (strict)
            // Large multipliers: pool needs to cover only a fraction (lenient, since they're rare)
            $solvencyFactor = match(true) {
                $m >= 500  => 0.40,  // pool needs only 40% of payout (jackpot: rare, pool recovers)
                $m >= 250  => 0.50,  // pool needs 50% of payout
                $m >= 100  => 0.67,  // pool needs 67% of payout
                default    => 1.05,  // pool needs 105% of payout (small wins: strict)
            };
            
            if ($payout > $totalPoolBalance / $solvencyFactor) {
                $weights[] = 0;
                continue;
            }

            // Get base weight from configurable settings
            $baseWeight = $configWeights[$m] ?? 500;
            
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
        // Get configurable bet gates
        $minBets100x = FairLuckSetting::getMinBetsFor100x();
        $minBets500x = FairLuckSetting::getMinBetsFor500x();

        // Bet count gates for high multipliers
        if ($betCount < $minBets100x && $multiplier > 100) return 0;
        if ($betCount < $minBets500x && $multiplier > 500) return 0;

        if ($rtpGap > 0.15) {
            // Significant deficit - boost tiers moderately to avoid overshooting
            // Cap rtpGap at 0.30 to prevent extreme boosts for brand-new users
            $cappedGap = min(0.30, $rtpGap);
            $gapBoost = min(3.0, $cappedGap * 10);

            if ($multiplier >= 250) {
                return $baseWeight * $gapBoost * 1.5;
            } elseif ($multiplier >= 50) {
                return $baseWeight * $gapBoost * 1.2;
            } else {
                return $baseWeight * $gapBoost;
            }
        }

        if ($rtpGap > 0.05) {
            // Moderate deficit
            $gapBoost = 1 + $rtpGap * 6;

            if ($multiplier >= 250) {
                return $baseWeight * $gapBoost * 1.5;
            } elseif ($multiplier >= 50) {
                return $baseWeight * $gapBoost * 1.3;
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

    /**
     * Get expected multiplier for probability calculation
     */
    public function getExpectedMultiplier(float $betAmount = 100, int $totalPoolBalance = 10000): float
    {
        $multipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        $totalWeight = 0;
        $totalValue = 0;

        // Get configurable weights
        $configWeights = FairLuckSetting::getMultiplierWeights();

        // Get wallet-based max multiplier
        $maxMultiplier = BankruptcyProtection::getMaxMultiplierForHealth();

        foreach ($multipliers as $m) {
            // Skip if multiplier exceeds wallet health limit
            if ($m > $maxMultiplier) {
                continue;
            }
            
            $payout = $m * $betAmount;

                // Pool solvency check - same as in select method (fixed)
            $solvencyFactor = match(true) {
                $m >= 500  => 0.40,
                $m >= 250  => 0.50,
                $m >= 100  => 0.67,
                default    => 1.05,
            };
            
            if ($payout > $totalPoolBalance / $solvencyFactor) {
                continue;
            }

            $weight = $configWeights[$m] ?? 500;
            $totalWeight += $weight;
            $totalValue += ($m * $weight);
        }

        return $totalWeight > 0 ? ($totalValue / $totalWeight) : 50.0;
    }

    /**
     * Validate pool can afford the payout. If not, fall back to lower multiplier.
     * NEVER cancels a win - always finds the highest affordable multiplier.
     * 
     * Now integrates with wallet health to apply appropriate limits.
     */
    public function validateAndFallback(int $selectedMultiplier, float $betAmount, int $totalPoolBalance, int $userId = null, int $betCount = null): int
    {
        $allMultipliers = [1000, 500, 250, 100, 70, 50, 20, 10, 5];
        $negativeLimit = BankruptcyProtection::getNegativeLimit();
        $effectiveBalance = $totalPoolBalance + $negativeLimit;
        
        // Get wallet-based max multiplier
        $maxMultiplier = BankruptcyProtection::getMaxMultiplierForHealth();

        foreach ($allMultipliers as $m) {
            // Skip if above selected multiplier
            if ($m > $selectedMultiplier) continue;
            
            // Skip if above wallet health limit
            if ($m > $maxMultiplier) continue;

            $payout = $m * $betAmount;
            
            // POOL SOLVENCY: High multipliers need extra safety margin
            $safetyMargin = match(true) {
                $m >= 1000 => 2.0,  // 100% safety margin
                $m >= 500  => 1.5,  // 50% safety margin
                $m >= 250  => 1.2,  // 20% safety margin
                default    => 1.0,  // No extra margin
            };

            $requiredBalance = $payout * $safetyMargin;
            
            if ($effectiveBalance >= $requiredBalance) {
                // POST-JACKPOT COOLDOWN: Check if user is in cooldown
                if ($userId !== null && $betCount !== null && $m >= 250) {
                    $cooldown = app(PostJackpotCooldown::class);
                    $m = $cooldown->applyRestriction($userId, $betCount, $m);
                    
                    if ($m === 0) {
                        continue; // Skip this multiplier, try next lower one
                    }
                }
                
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
