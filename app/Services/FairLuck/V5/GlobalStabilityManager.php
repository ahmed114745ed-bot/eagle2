<?php

namespace App\Services\FairLuck\V5;

use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Global Stability Manager (Pillar A)
 * 
 * Monitors the Total Platform RTP (Sum of all users' payouts / Sum of all users' bets).
 * If the global RTP exceeds 0.95, it returns a GlobalAdjustment factor (e.g., 0.8) 
 * to throttle win probabilities system-wide.
 */
class GlobalStabilityManager
{
    private const CACHE_KEY = 'fairluck5:global_rtp';
    private const CACHE_TTL = 300; // 5 minutes
    
    private const RTP_THRESHOLD = 0.95;
    private const MIN_ADJUSTMENT = 0.6;
    private const MAX_ADJUSTMENT = 1.0;
    
    private const GLOBAL_VAULT_SAFETY_THRESHOLD = 10000;
    
    /**
     * Get the global adjustment factor based on platform-wide RTP.
     * 
     * @return float Always returns 1.0 (Open Mathematical Model)
     */
    public function getGlobalAdjustment(): float
    {
        return 1.0;
    }
    
    /**
     * Calculate the global RTP across all users.
     * Uses caching to avoid expensive database queries on every bet.
     * 
     * @return float Global RTP (0.0 to 2.0+ range)
     */
    public function calculateGlobalRTP(): float
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->computeGlobalRTPFromDatabase();
        });
    }
    
    /**
     * Compute global RTP from database transactions.
     * 
     * @return float
     */
    private function computeGlobalRTPFromDatabase(): float
    {
        // Get aggregated data from last 24 hours for recent RTP
        $stats = FairLuckTransaction::query()
            ->where('created_at', '>=', now()->subHours(24))
            ->selectRaw('SUM(bet_amount) as total_bets, SUM(CASE WHEN is_winner = 1 THEN bet_amount * multiplier ELSE 0 END) as total_payouts')
            ->first();
        
        $totalBets = (float) ($stats->total_bets ?? 0);
        $totalPayouts = (float) ($stats->total_payouts ?? 0);
        
        if ($totalBets <= 0) {
            return 0.90; // Default healthy RTP when no data
        }
        
        return $totalPayouts / $totalBets;
    }
    
    /**
     * Force refresh the global RTP cache.
     * 
     * @return float New global RTP value
     */
    public function refreshGlobalRTP(): float
    {
        Cache::forget(self::CACHE_KEY);
        return $this->calculateGlobalRTP();
    }
    
    /**
     * Check if the global vault is below safety threshold.
     * This triggers more aggressive profit extraction.
     * 
     * @return bool
     */
    public function isGlobalVaultBelowSafety(): bool
    {
        $balance = FairLuckWallet::getVaultBalance();
        return $balance < self::GLOBAL_VAULT_SAFETY_THRESHOLD;
    }
    
    /**
     * Get the current global vault balance.
     * 
     * @return int
     */
    public function getGlobalVaultBalance(): int
    {
        return FairLuckWallet::getVaultBalance();
    }
    
    /**
     * Get comprehensive stability metrics for monitoring.
     * 
     * @return array
     */
    public function getStabilityMetrics(): array
    {
        $globalRTP = $this->calculateGlobalRTP();
        $adjustment = $this->getGlobalAdjustment();
        $vaultBalance = $this->getGlobalVaultBalance();
        $isBelowSafety = $this->isGlobalVaultBelowSafety();
        
        return [
            'global_rtp' => round($globalRTP, 4),
            'rtp_threshold' => self::RTP_THRESHOLD,
            'global_adjustment' => round($adjustment, 4),
            'vault_balance' => $vaultBalance,
            'vault_safety_threshold' => self::GLOBAL_VAULT_SAFETY_THRESHOLD,
            'is_below_safety' => $isBelowSafety,
            'status' => $this->determineStatus($globalRTP, $isBelowSafety),
        ];
    }
    
    /**
     * Determine the overall system status.
     * 
     * @param float $globalRTP
     * @param bool $isBelowSafety
     * @return string
     */
    private function determineStatus(float $globalRTP, bool $isBelowSafety): string
    {
        if ($isBelowSafety) {
            return 'CRITICAL';
        }
        
        if ($globalRTP > 1.0) {
            return 'DANGER';
        }
        
        if ($globalRTP > self::RTP_THRESHOLD) {
            return 'WARNING';
        }
        
        if ($globalRTP > 0.85) {
            return 'HEALTHY';
        }
        
        return 'CONSERVATIVE';
    }
}
