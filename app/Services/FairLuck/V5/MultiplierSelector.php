<?php

namespace App\Services\FairLuck\V5;

use App\Models\FairLuckWallet;

/**
 * Multiplier Selector with Weighted Scaling (Pillar B)
 * 
 * Implements "Varying Increase Extent" logic using a curve/scaling approach.
 * When a user is in a deficit (negative deviation):
 * - Boost Small Multipliers (5x, 10x) by 40%
 * - Boost Medium Multipliers (50x, 100x) by 20%
 * - Boost Jackpots (250x+) by 500% (Nuclear Compensation)
 * 
 * Uses smooth curve transitions, not binary 0/1 switches.
 */
class MultiplierSelector
{
    /**
     * Available multipliers in the system.
     */
    private const MULTIPLIERS = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
    
    /**
     * Base weights for each multiplier (natural distribution).
     */
    private const BASE_WEIGHTS = [
        5 => 2800,
        10 => 1800,
        20 => 1200,
        50 => 700,
        70 => 400,
        100 => 200,
        250 => 80,
        500 => 20,
        1000 => 5,
    ];
    
    /**
     * Multiplier tier classifications.
     */
    private const TIER_SMALL = [5, 10, 20];
    private const TIER_MEDIUM = [50, 70, 100];
    private const TIER_JACKPOT = [250, 500, 1000];
    
    /**
     * Boost percentages for deficit users (negative deviation).
     */
    private const DEFICIT_BOOST_SMALL = 0.40;   // 40% boost
    private const DEFICIT_BOOST_MEDIUM = 0.20;  // 20% boost
    private const DEFICIT_BOOST_JACKPOT = 5.0;  // 500% boost - Nuclear Compensation
    
    /**
     * Select a multiplier based on user's deviation and context.
     * 
     * @param float $deviation User's current deviation
     * @param float $globalAdjustment Global stability adjustment factor
     * @param array $context Additional context (forceJackpot, consecutiveLosses, etc.)
     * @return int Selected multiplier
     */
    /**
     * Get the next lower multiplier for downshifting logic.
     */
    public function getNextLowerMultiplier(int $currentMultiplier): ?int
    {
        $multipliers = self::MULTIPLIERS;
        rsort($multipliers);
        
        foreach ($multipliers as $m) {
            if ($m < $currentMultiplier) {
                return $m;
            }
        }
        
        return null;
    }

    public function selectMultiplier(
        float $deviation,
        float $globalAdjustment = 1.0,
        array $context = []
    ): int {
        $weights = $this->calculateWeights($deviation, $globalAdjustment, $context);
        
        // Validate multiplier availability based on wallet balances
        $betAmount = $context['bet_amount'] ?? 100;
        $weights = $this->validateWalletAvailability($weights, $betAmount);
        
        return $this->weightedRandom(self::MULTIPLIERS, $weights);
    }
    
    /**
     * Calculate weights for all multipliers using curve-based scaling.
     * 
     * @param float $deviation User's deviation (-1.0 to 1.0 range typically)
     * @param float $globalAdjustment Global adjustment factor (0.6 to 1.0)
     * @param array $context Additional context
     * @return array Weights indexed by multiplier
     */
    public function calculateWeights(
        float $deviation,
        float $globalAdjustment = 1.0,
        array $context = []
    ): array {
        $weights = [];
        
        // Calculate deficit intensity using a smooth curve
        // Negative deviation = user is in deficit (lost more than expected)
        $deficitIntensity = $this->calculateDeficitIntensity($deviation);
        
        foreach (self::MULTIPLIERS as $multiplier) {
            $baseWeight = self::BASE_WEIGHTS[$multiplier];
            
            // Apply tier-specific boost based on deficit intensity
            $tierBoost = $this->getTierBoost($multiplier, $deficitIntensity);
            
            // Apply global adjustment (throttles all wins when platform RTP is high)
            $adjustedWeight = $baseWeight * (1 + $tierBoost) * $globalAdjustment;
            
            // Apply context-specific modifiers
            $adjustedWeight = $this->applyContextModifiers($multiplier, $adjustedWeight, $context);
            
            $weights[$multiplier] = max(0, (int) round($adjustedWeight));
        }
        
        return $weights;
    }
    
    /**
     * Calculate deficit intensity based on the -0.05 threshold.
     * Returns a value between 0.0 and 1.0.
     * 
     * @param float $deviation User's deviation
     * @return float Deficit intensity (0.0 to 1.0)
     */
    private function calculateDeficitIntensity(float $deviation): float
    {
        // Start applying boost when user deviation hits the -0.05 deficit threshold
        if ($deviation >= -0.05) {
            return 0.0;
        }
        
        // Intensity increases sharply after -0.05 to ensure full boost at -0.10
        $absDeficit = abs($deviation + 0.05);
        $intensity = $absDeficit * 20; // 0.0 at -0.05, 1.0 at -0.10
        
        return min(1.0, max(0.0, $intensity));
    }
    
    /**
     * Get the tier-specific boost based on deficit intensity.
     * Implements specific V5 "Magnet Effect" multipliers.
     * 
     * @param int $multiplier The multiplier value
     * @param float $deficitIntensity Deficit intensity (0.0 to 1.0)
     * @return float Boost factor (fractional addition to weight)
     */
    private function getTierBoost(int $multiplier, float $deficitIntensity): float
    {
        if ($deficitIntensity <= 0) {
            return 0.0;
        }
        
        // Implement specific multipliers from prompt:
        // 5x and 10x -> 1.4 (40% boost)
        // 250x+ -> 1.05 (5% boost)
        
        if (in_array($multiplier, [5, 10])) {
            $maxBoost = self::DEFICIT_BOOST_SMALL; // 0.40
        } elseif (in_array($multiplier, self::TIER_JACKPOT)) {
            $maxBoost = self::DEFICIT_BOOST_JACKPOT; // 5.0 (500% weight)
        } elseif (in_array($multiplier, self::TIER_MEDIUM)) {
            $maxBoost = self::DEFICIT_BOOST_MEDIUM; // 0.20
        } else {
            $maxBoost = 0.25; // Default 25% boost - Open Mathematical Model
        }
        
        return $maxBoost * $deficitIntensity;
    }
    
    /**
     * Apply context-specific modifiers to weight.
     * 
     * @param int $multiplier
     * @param float $weight
     * @param array $context
     * @return float Modified weight
     */
    private function applyContextModifiers(int $multiplier, float $weight, array $context): float
    {
        // Force jackpot mode
        if (!empty($context['force_jackpot']) && $multiplier < 250) {
            return 0;
        }
        
        // Force mini wins mode (for near-bankrupt users)
        if (!empty($context['force_mini_wins']) && $multiplier > 20) {
            return $weight * 0.1;
        }
        
        // Drain lock mode (user recently won big, reduce high multipliers)
        if (!empty($context['is_drain_locked'])) {
            if ($multiplier >= 250) {
                return 0;
            }
            if ($multiplier >= 50) {
                return $weight * 0.3;
            }
        }
        
        // Consecutive losses boost
        $consecutiveLosses = $context['consecutive_losses'] ?? 0;
        if ($consecutiveLosses >= 5 && in_array($multiplier, self::TIER_SMALL)) {
            $lossBoost = 1 + min(0.5, $consecutiveLosses * 0.05);
            $weight *= $lossBoost;
        }
        
        // Beginner protection boost
        if (!empty($context['is_beginner']) && $multiplier <= 100) {
            $weight *= 1.3;
        }
        
        // Pity count boost for jackpots
        $pityCount = $context['pity_count'] ?? 0;
        if ($pityCount > 600 && in_array($multiplier, self::TIER_JACKPOT)) {
            $pityBoost = 1 + min(1.5, ($pityCount - 600) / 400);
            $weight *= $pityBoost;
        }
        
        return $weight;
    }
    
    /**
     * Validate and adjust weights based on wallet availability.
     * 
     * @param array $weights
     * @param float $betAmount
     * @return array Adjusted weights
     */
    private function validateWalletAvailability(array $weights, float $betAmount): array
    {
        $vaultBalance = FairLuckWallet::getVaultBalance();
        $negativeLimit = FairLuckWallet::getNegativeLimit();
        $availableLiquidity = $vaultBalance + $negativeLimit;
        
        foreach ($weights as $multiplier => $weight) {
            if ($weight <= 0) continue;
            
            $requiredPayout = max(0, ($multiplier - 1) * $betAmount);
            
            // Check if unified vault can afford the payout
            if ($availableLiquidity < $requiredPayout) {
                // Cannot afford: Zero out jackpots, heavily throttle others
                if (in_array($multiplier, self::TIER_JACKPOT)) {
                    $weights[$multiplier] = 0;
                } else {
                    $weights[$multiplier] = (int) ($weight * 0.1);
                }
            } elseif ($availableLiquidity < $requiredPayout * 2.5) {
                // Precautious throttling for higher multipliers if liquidity is thin
                if ($multiplier >= 50) {
                    $weights[$multiplier] = (int) ($weight * 0.6);
                }
            }
        }
        
        return $weights;
    }
    
    /**
     * Perform weighted random selection.
     * 
     * @param array $values
     * @param array $weights
     * @return int Selected value
     */
    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        
        if ($totalWeight <= 0) {
            return $values[0]; // Fallback to smallest multiplier
        }
        
        $random = mt_rand(1, (int) $totalWeight);
        $currentWeight = 0;
        
        foreach ($values as $value) {
            $currentWeight += $weights[$value] ?? 0;
            if ($random <= $currentWeight) {
                return $value;
            }
        }
        
        // Fallback: return first available multiplier
        foreach ($values as $value) {
            if (($weights[$value] ?? 0) > 0) {
                return $value;
            }
        }
        
        return $values[0];
    }
    
    /**
     * Get the expected multiplier for probability calculations.
     * 
     * @return float
     */
    public function getExpectedMultiplier(): float
    {
        $totalWeight = array_sum(self::BASE_WEIGHTS);
        $expectedValue = 0;
        
        foreach (self::BASE_WEIGHTS as $multiplier => $weight) {
            $expectedValue += $multiplier * ($weight / $totalWeight);
        }
        
        return $expectedValue;
    }
    
    /**
     * Get available multipliers.
     * 
     * @return array
     */
    public function getAvailableMultipliers(): array
    {
        return self::MULTIPLIERS;
    }
    
    /**
     * Get weight distribution for debugging/monitoring.
     * 
     * @param float $deviation
     * @param float $globalAdjustment
     * @return array
     */
    public function getWeightDistribution(float $deviation, float $globalAdjustment = 1.0): array
    {
        $weights = $this->calculateWeights($deviation, $globalAdjustment);
        $totalWeight = array_sum($weights);
        
        $distribution = [];
        foreach ($weights as $multiplier => $weight) {
            $distribution[$multiplier] = [
                'weight' => $weight,
                'probability' => $totalWeight > 0 ? round($weight / $totalWeight * 100, 2) . '%' : '0%',
            ];
        }
        
        return [
            'deviation' => $deviation,
            'deficit_intensity' => $this->calculateDeficitIntensity($deviation),
            'global_adjustment' => $globalAdjustment,
            'total_weight' => $totalWeight,
            'multipliers' => $distribution,
        ];
    }
}
