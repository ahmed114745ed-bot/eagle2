<?php

namespace App\Services\FairLuck\V5;

use App\Models\UserLuckProfile;

/**
 * Probability Engine with Holistic Individual RTP (Pillar E)
 * 
 * Calculates win probability respecting user's lifetime historical data.
 * Integrates with GlobalStabilityManager for platform-wide adjustments.
 * Uses holistic approach considering data from January 2025 to January 2026.
 */
class ProbabilityEngine
{
    /**
     * Target RTP for the platform.
     */
    private const TARGET_RTP = 0.90;
    
    /**
     * Probability bounds.
     */
    private const MIN_PROBABILITY = 0.05;
    private const MAX_PROBABILITY = 0.98;
    
    /**
     * Deviation thresholds for probability adjustments.
     */
    private const DEVIATION_GENEROUS_THRESHOLD = -0.15;
    private const DEVIATION_RECOVERY_THRESHOLD = 0.05;
    private const DEVIATION_STRICT_THRESHOLD = 0.15;
    
    private GlobalStabilityManager $stabilityManager;
    private DeviationCalculator $deviationCalculator;
    
    public function __construct(
        GlobalStabilityManager $stabilityManager,
        DeviationCalculator $deviationCalculator
    ) {
        $this->stabilityManager = $stabilityManager;
        $this->deviationCalculator = $deviationCalculator;
    }
    
    /**
     * Calculate the final win probability using the "Gaming Company" model.
     * 
     * @param float $betImpact The intensity needed to reach Target RTP
     * @param float $deviation User's current deviation
     * @param float $protectionMultiplier Beginner protection multiplier
     * @param float $userBalance User's current balance
     * @param float $netBet Net bet amount
     * @param float $globalAdjustment Global stability adjustment factor
     * @param array $context Additional context (actual_rtp, is_drain_locked, etc.)
     * @return float Final probability (0.05 to 0.95)
     */
    public function calculate(
        float $betImpact,
        float $deviation,
        float $protectionMultiplier,
        float $userBalance,
        float $netBet,
        float $globalAdjustment = 1.0,
        array $context = []
    ): float {
        // Step 1: Base probability based on RTP Gap (Open Mathematical Model)
        // betImpact is (TargetRTP - ActualRTP) / NetBet (pre-calculated in Service)
        // We want a strong response: reach 0.95 quickly if deficit is large.
        
        $targetRTP = (float) \App\Models\FairLuckSetting::getByKey('target_rtp', 0.95);
        $actualRTP = $context['actual_rtp'] ?? 1.0;
        $rtpGap = $targetRTP - $actualRTP;

        // Base probability depends on RTP status
        $baseProb = ($rtpGap > 0) ? 0.30 : 0.15;

        // Aggressive Response Logic: Probability = Base + (abs(rtpGap) / netBet)
        // Note: Using currency-based gap for high intensity
        if ($rtpGap > 0) {
            $deficitAmount = $rtpGap * ($context['total_bets'] ?? $netBet);
            $baseProb += ($deficitAmount / ($netBet > 0 ? $netBet : 1));
        } else {
            $surplusAmount = abs($rtpGap) * ($context['total_bets'] ?? $netBet);
            $baseProb -= ($surplusAmount / ($netBet > 0 ? $netBet : 1));
        }
        
        // Step 2: Refine with Deviation (Pillar E refinement)
        $deviationAdjusted = $this->applyDeviationAdjustment($baseProb, $deviation);
        
        // Step 3: Apply beginner protection
        $protectionAdjusted = $deviationAdjusted * $protectionMultiplier;
        
        // Step 4: Remove binary locks - only scale down if significantly above target
        if ($actualRTP > ($targetRTP * 1.5)) {
            $overage = $actualRTP - $targetRTP;
            $reduction = min(0.7, $overage * 0.3); 
            $protectionAdjusted *= (1 - $reduction);
        }
        
        // Step 5: Global adjustment is always 1.0 from StabilityManager, so we just multiply
        $globalAdjusted = $protectionAdjusted * $globalAdjustment;
        
        // Step 6: Apply balance-based safety net (keeping for user experience)
        $balanceAdjusted = $this->applyBalanceSafetyNet($globalAdjusted, $userBalance, $netBet);
        
        // Step 7: Clamp to valid probability range
        return $this->clampProbability($balanceAdjusted);
    }
    
    /**
     * Calculate probability using holistic lifetime data.
     * 
     * @param UserLuckProfile $profile User's luck profile with lifetime data
     * @param float $betAmount Current bet amount
     * @param float $expectedMultiplier Expected multiplier for RTP calculation
     * @param float $protectionMultiplier Beginner protection multiplier
     * @return float Final probability
     */
    public function calculateFromProfile(
        UserLuckProfile $profile,
        float $betAmount,
        float $expectedMultiplier,
        float $protectionMultiplier = 1.0
    ): float {
        // Calculate deviation from lifetime data (holistic approach)
        $deviation = $this->deviationCalculator->calculateFromProfile($profile);
        
        // Gaming Company Logic for calculation from profile
        $actualRTP = $profile->total_bets > 0 ? ($profile->total_profit / $profile->total_bets) : 0;
        $targetRTP = (float) \App\Models\FairLuckSetting::getByKey('target_rtp', 0.95);
        $rtpGap = $targetRTP - $actualRTP;
        $betImpact = $rtpGap / ($betAmount > 0 ? $betAmount : 1);
        
        // Get current global adjustment
        $globalAdjustment = $this->stabilityManager->getGlobalAdjustment();
        
        return $this->calculate(
            $betImpact,
            $deviation,
            $protectionMultiplier,
            (float) $profile->user?->di ?? 0,
            $betAmount,
            $globalAdjustment,
            ['actual_rtp' => $actualRTP]
        );
    }
    
    /**
     * Apply deviation-based probability adjustment.
     * 
     * @param float $baseProbability
     * @param float $deviation
     * @return float Adjusted probability
     */
    private function applyDeviationAdjustment(float $baseProbability, float $deviation): float
    {
        // Generous mode: user is in significant deficit
        if ($deviation <= self::DEVIATION_GENEROUS_THRESHOLD) {
            $boostFactor = 1 + (abs($deviation) * 0.5);
            return $baseProbability * min(2.0, $boostFactor);
        }
        
        // Recovery mode: user is slightly in deficit
        if ($deviation < 0) {
            $boostFactor = 1 + (abs($deviation) * 0.3);
            return $baseProbability * $boostFactor;
        }
        
        // Neutral: user is at expected RTP
        if ($deviation < self::DEVIATION_RECOVERY_THRESHOLD) {
            return $baseProbability;
        }
        
        // Recovery mode: user is winning more than expected
        if ($deviation < self::DEVIATION_STRICT_THRESHOLD) {
            $reductionFactor = 1 - ($deviation * 0.5);
            return $baseProbability * max(0.5, $reductionFactor);
        }
        
        // Strict mode: user is significantly above expected RTP
        $reductionFactor = 1 - min(0.7, $deviation * 0.8);
        return $baseProbability * max(0.3, $reductionFactor);
    }
    
    /**
     * Apply balance-based safety net.
     * Prevents users from going completely bankrupt.
     * 
     * @param float $probability
     * @param float $userBalance
     * @param float $betAmount
     * @return float Adjusted probability
     */
    private function applyBalanceSafetyNet(float $probability, float $userBalance, float $betAmount): float
    {
        if ($userBalance <= 0 || $betAmount <= 0) {
            return $probability;
        }
        
        $betsRemaining = $userBalance / $betAmount;
        
        // If user can only afford a few more bets, boost probability
        if ($betsRemaining <= 5) {
            return max($probability, 0.25);
        }
        
        if ($betsRemaining <= 10) {
            return max($probability, 0.18);
        }
        
        if ($betsRemaining <= 20) {
            return max($probability, 0.12);
        }
        
        return $probability;
    }
    
    /**
     * Get target RTP based on user's deviation.
     * 
     * @param float $deviation
     * @return float Target RTP (0.0 to 1.0)
     */
    private function getTargetRTPForUser(float $deviation): float
    {
        // Users in deficit get higher target RTP
        if ($deviation <= self::DEVIATION_GENEROUS_THRESHOLD) {
            return 0.95;
        }
        
        if ($deviation < 0) {
            // Linear interpolation from 0.90 to 0.95
            $factor = abs($deviation) / abs(self::DEVIATION_GENEROUS_THRESHOLD);
            return self::TARGET_RTP + ($factor * 0.05);
        }
        
        // Users above expected get lower target RTP
        if ($deviation >= self::DEVIATION_STRICT_THRESHOLD) {
            return 0.70;
        }
        
        if ($deviation > 0) {
            // Linear interpolation from 0.90 to 0.70
            $factor = $deviation / self::DEVIATION_STRICT_THRESHOLD;
            return self::TARGET_RTP - ($factor * 0.20);
        }
        
        return self::TARGET_RTP;
    }
    
    /**
     * Clamp probability to valid range.
     * 
     * @param float $probability
     * @return float Clamped probability
     */
    private function clampProbability(float $probability): float
    {
        return max(self::MIN_PROBABILITY, min(self::MAX_PROBABILITY, $probability));
    }
    
    /**
     * Calculate probability with consecutive loss consideration.
     * 
     * @param float $baseProbability
     * @param int $consecutiveLosses
     * @return float Adjusted probability
     */
    public function applyLossStreakBoost(float $baseProbability, int $consecutiveLosses): float
    {
        if ($consecutiveLosses < 5) {
            return $baseProbability;
        }
        
        // Gradual boost based on loss streak
        if ($consecutiveLosses >= 25) {
            return 1.0; // Guaranteed win
        }
        
        if ($consecutiveLosses >= 15) {
            return max($baseProbability, 0.80);
        }
        
        if ($consecutiveLosses >= 10) {
            return max($baseProbability, 0.60);
        }
        
        // 5-9 consecutive losses
        $boost = 0.50 + (($consecutiveLosses - 5) * 0.02);
        return max($baseProbability, $boost);
    }
    
    /**
     * Get probability calculation breakdown for debugging.
     * 
     * @param float $baseProbability
     * @param float $deviation
     * @param float $protectionMultiplier
     * @param float $userBalance
     * @param float $betAmount
     * @return array
     */
    public function getProbabilityBreakdown(
        float $baseProbability,
        float $deviation,
        float $protectionMultiplier,
        float $userBalance,
        float $betAmount
    ): array {
        $deviationAdjusted = $this->applyDeviationAdjustment($baseProbability, $deviation);
        $protectionAdjusted = $deviationAdjusted * $protectionMultiplier;
        $globalAdjustment = $this->stabilityManager->getGlobalAdjustment();
        $globalAdjusted = $protectionAdjusted * $globalAdjustment;
        $balanceAdjusted = $this->applyBalanceSafetyNet($globalAdjusted, $userBalance, $betAmount);
        $finalProbability = $this->clampProbability($balanceAdjusted);
        
        return [
            'base_probability' => round($baseProbability, 4),
            'deviation' => round($deviation, 4),
            'deviation_adjusted' => round($deviationAdjusted, 4),
            'protection_multiplier' => round($protectionMultiplier, 4),
            'protection_adjusted' => round($protectionAdjusted, 4),
            'global_adjustment' => round($globalAdjustment, 4),
            'global_adjusted' => round($globalAdjusted, 4),
            'user_balance' => $userBalance,
            'bet_amount' => $betAmount,
            'balance_adjusted' => round($balanceAdjusted, 4),
            'final_probability' => round($finalProbability, 4),
            'target_rtp' => round($this->getTargetRTPForUser($deviation), 4),
        ];
    }
    
    /**
     * Determine the mood/mode based on probability and deviation.
     * 
     * @param float $finalProbability
     * @param float $deviation
     * @return string
     */
    public function determineMood(float $finalProbability, float $deviation): string
    {
        if ($deviation <= self::DEVIATION_GENEROUS_THRESHOLD) {
            return 'Generous';
        }
        
        if ($deviation >= self::DEVIATION_STRICT_THRESHOLD) {
            return 'Recovery';
        }
        
        if ($finalProbability >= 0.5) {
            return 'Favorable';
        }
        
        if ($finalProbability >= 0.3) {
            return 'Stable';
        }
        
        return 'Conservative';
    }
}
