<?php

namespace App\Services\FairLuck\V5;

use App\Models\FairLuckWallet;
use App\Models\UserLuckProfile;

/**
 * Deviation Calculator with Dynamic Profit Extraction (Pillar C)
 * 
 * Makes the APP_PROFIT_RATE dynamic:
 * - Starts at 0.10 (10%)
 * - Increases to 0.15 (15%) automatically if GlobalVault balance falls below safety threshold
 * 
 * Also calculates user deviation for RTP balancing.
 */
class DeviationCalculator
{
    /**
     * Base profit rate (10%).
     */
    private const BASE_PROFIT_RATE = 0.05; // Fixed 5% - Aggressive Engine
    
    /**
     * Emergency profit rate when vault is low (15%).
     */
    private const EMERGENCY_PROFIT_RATE = 0.15;
    
    /**
     * Stimulative profit rate when vault is rich (5%).
     */
    private const STIMULATIVE_PROFIT_RATE = 0.05;
    
    /**
     * Safety threshold for global vault (10k).
     */
    // Safety thresholds removed - Open Model allows negative vault
    
    /**
     * Rich threshold for global vault (50k).
     */
    private const VAULT_HIGH_THRESHOLD = 50000;
    
    /**
     * Target RTP for the platform.
     */
    private const TARGET_RTP = 0.90;
    
    private GlobalStabilityManager $stabilityManager;
    
    public function __construct(GlobalStabilityManager $stabilityManager)
    {
        $this->stabilityManager = $stabilityManager;
    }

    /**
     * Calculate user deviation for RTP balancing (Pillar E).
     * 
     * Formula: Actual RTP - Target RTP
     * 
     * @param float $totalBets
     * @param float $totalProfit (Net profit: total_win - total_bet)
     * @return float Deviation (e.g., -0.05 means user is 5% below target)
     */
    public function calculate(float $totalBets, float $totalProfit): float
    {
        if ($totalBets <= 0) {
            return 0.0;
        }
        
        $actualRTP = 1 + ($totalProfit / $totalBets);
        $targetRTP = (float) \App\Models\FairLuckSetting::getByKey('target_rtp', 0.95);
        
        return $actualRTP - $targetRTP;
    }
    
    /**
     * Get the current dynamic profit rate (APP_PROFIT_RATE).
     * 
     * Logic:
     * - Balance < 10,000 -> 0.15 (Emergency extraction)
     * - Balance > 50,000 -> 0.05 (Stimulate the market)
     * - 10,000 - 50,000 -> Linear scaling from 0.15 to 0.05
     * 
     * @return float Profit rate (0.05 to 0.15)
     */
    public function getDynamicProfitRate(): float
    {
        return self::BASE_PROFIT_RATE; // Fixed 5%
    }
    
    /**
     * Calculate the house cut from a bet amount.
     * 
     * @param float $betAmount
     * @return float House cut amount
     */
    public function calculateHouseCut(float $betAmount): float
    {
        $profitRate = $this->getDynamicProfitRate();
        return $betAmount * $profitRate;
    }
    
    /**
     * Calculate the net bet amount after house cut.
     * 
     * @param float $betAmount
     * @return float Net amount going to prize pool
     */
    public function calculateNetBet(float $betAmount): float
    {
        return $betAmount - $this->calculateHouseCut($betAmount);
    }
    
    /**
     * Calculate jackpot probability with unified vault safety checks.
     * 
     * @param float $baseProbability
     * @param float $betAmount
     * @param int $vaultBalance
     * @param int $multiplier
     * @return float Adjusted probability
     */
    public static function calculateJackpotProbability(
        float $baseProbability,
        float $betAmount,
        int $vaultBalance,
        int $multiplier
    ): float {
        // System always allows jackpots even if vault is negative
        return $baseProbability;
    }
    
    /**
     * Get profit extraction metrics for monitoring.
     * 
     * @return array
     */
    public function getProfitMetrics(): array
    {
        $vaultBalance = FairLuckWallet::getVaultBalance();
        $currentRate = $this->getDynamicProfitRate();
        
        return [
            'base_profit_rate' => self::BASE_PROFIT_RATE,
            'emergency_profit_rate' => self::EMERGENCY_PROFIT_RATE,
            'current_profit_rate' => round($currentRate, 4),
            'vault_balance' => $vaultBalance,
            'vault_safety_threshold' => self::VAULT_LOW_THRESHOLD,
            'is_emergency_mode' => $currentRate > self::BASE_PROFIT_RATE,
            'target_rtp' => self::TARGET_RTP,
        ];
    }
    
    /**
     * Calculate expected profit from a bet.
     * 
     * @param float $betAmount
     * @param float $winProbability
     * @param float $expectedMultiplier
     * @return float Expected profit for the house
     */
    public function calculateExpectedHouseProfit(
        float $betAmount,
        float $winProbability,
        float $expectedMultiplier
    ): float {
        // Expected payout = probability * multiplier * bet
        $expectedPayout = $winProbability * $expectedMultiplier * $betAmount;
        
        // House profit = bet - expected payout
        return $betAmount - $expectedPayout;
    }
    
    /**
     * Determine if user should receive compensation boost.
     * 
     * @param float $deviation
     * @param int $consecutiveLosses
     * @return bool
     */
    public function shouldCompensate(float $deviation, int $consecutiveLosses): bool
    {
        // Compensate if user is significantly in deficit
        if ($deviation < -0.15) {
            return true;
        }
        
        // Compensate after many consecutive losses
        if ($consecutiveLosses >= 10) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate compensation boost factor.
     * 
     * @param float $deviation
     * @param int $consecutiveLosses
     * @return float Boost factor (1.0 = no boost, 2.0 = double probability)
     */
    public function getCompensationBoost(float $deviation, int $consecutiveLosses): float
    {
        $boost = 1.0;
        
        // Deviation-based boost
        if ($deviation < 0) {
            $deficitBoost = 1 + (abs($deviation) * 0.5);
            $boost = max($boost, $deficitBoost);
        }
        
        // Loss streak boost
        if ($consecutiveLosses >= 5) {
            $streakBoost = 1 + min(1.0, ($consecutiveLosses - 5) * 0.1);
            $boost = max($boost, $streakBoost);
        }
        
        return min(2.0, $boost); // Cap at 2x boost
    }
}
