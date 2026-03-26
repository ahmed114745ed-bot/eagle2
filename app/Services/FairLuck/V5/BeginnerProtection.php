<?php

namespace App\Services\FairLuck\V5;


use App\Models\FairLuckSetting;
use App\Models\UserLuckProfile;

/**
 * Beginner Protection with Gradual Transition (Pillar D)
 * 
 * Provides protection for new users with a seamless fade-out:
 * - Full protection during initial period (7 days or 50 bets)
 * - Gradual transition over 10 bets instead of stopping abruptly
 * - Smooth multiplier decay from boost to normal
 */
class BeginnerProtection
{
    /**
     * Default protection settings.
     */
    private const DEFAULT_PROTECTION_DAYS = 7;
    private const DEFAULT_PROTECTION_BETS = 50;
    private const DEFAULT_MAX_PROFIT_RATE = 0.20;
    private const DEFAULT_BOOST_MULTIPLIER = 2.0;
    
    /**
     * Gradual transition settings.
     */
    private const TRANSITION_BETS = 10;
    
    /**
     * Get the protection multiplier for a user.
     * Implements gradual transition logic.
     * 
     * @param UserLuckProfile $profile
     * @return float Multiplier (1.0 = no boost, 2.0 = full boost)
     */
    public function getMultiplier(UserLuckProfile $profile): float
    {
        // Legacy users don't get protection
        if ($profile->is_legacy_user) {
            return 1.0;
        }
        
        // New user who hasn't bet yet gets full protection
        if (!$profile->first_bet_at) {
            return $this->getBoostMultiplier();
        }
        
        $daysLimit = $this->getProtectionDays();
        $betsLimit = $this->getProtectionBets();
        $maxProfitRate = $this->getMaxProfitRate();
        $boostMultiplier = $this->getBoostMultiplier();
        
        // Calculate current status
        $daysSinceFirst = $profile->first_bet_at->diffInDays(now());
        $betsCount = $profile->bet_count;
        $profitRate = $profile->total_bets > 0 
            ? $profile->total_profit / $profile->total_bets 
            : 0;
        
        // Check if user exceeded profit limit (no more protection)
        if ($profitRate >= $maxProfitRate) {
            return 1.0;
        }
        
        // Check if user is still in full protection period
        if ($daysSinceFirst < $daysLimit && $betsCount < $betsLimit) {
            return $boostMultiplier;
        }
        
        // GRADUAL TRANSITION LOGIC
        // Fade out over 10 bets after protection ends
        
        // Calculate how many bets past the protection limit
        $betsOverLimit = max(0, $betsCount - $betsLimit);
        
        // Also consider days over limit
        $daysOverLimit = max(0, $daysSinceFirst - $daysLimit);
        
        // Use the more restrictive of the two
        $transitionProgress = 0.0;
        
        if ($betsCount >= $betsLimit) {
            // Transition based on bets
            $transitionProgress = min(1.0, $betsOverLimit / self::TRANSITION_BETS);
        }
        
        if ($daysSinceFirst >= $daysLimit) {
            // Also consider days-based transition (1 day = 2 bets worth)
            $daysTransition = min(1.0, ($daysOverLimit * 2) / self::TRANSITION_BETS);
            $transitionProgress = max($transitionProgress, $daysTransition);
        }
        
        // If still within transition period, apply gradual fade
        if ($transitionProgress < 1.0) {
            // Use smooth easing curve for natural feel
            $easedProgress = $this->easeOutQuad($transitionProgress);
            
            // Interpolate from boost to 1.0
            return $boostMultiplier - ($easedProgress * ($boostMultiplier - 1.0));
        }
        
        // Fully transitioned, no more protection
        return 1.0;
    }
    
    /**
     * Check if user is under any form of protection (full or transitional).
     * 
     * @param UserLuckProfile $profile
     * @return bool
     */
    public function isUnderProtection(UserLuckProfile $profile): bool
    {
        return $this->getMultiplier($profile) > 1.0;
    }
    
    /**
     * Check if user is in full protection (not transitioning).
     * 
     * @param UserLuckProfile $profile
     * @return bool
     */
    public function isFullyProtected(UserLuckProfile $profile): bool
    {
        return $this->getMultiplier($profile) >= $this->getBoostMultiplier();
    }
    
    /**
     * Check if user is in transition phase.
     * 
     * @param UserLuckProfile $profile
     * @return bool
     */
    public function isInTransition(UserLuckProfile $profile): bool
    {
        $multiplier = $this->getMultiplier($profile);
        return $multiplier > 1.0 && $multiplier < $this->getBoostMultiplier();
    }
    
    /**
     * Get protection status details for a user.
     * 
     * @param UserLuckProfile $profile
     * @return array
     */
    public function getProtectionStatus(UserLuckProfile $profile): array
    {
        $multiplier = $this->getMultiplier($profile);
        $boostMultiplier = $this->getBoostMultiplier();
        
        $status = 'none';
        if ($multiplier >= $boostMultiplier) {
            $status = 'full';
        } elseif ($multiplier > 1.0) {
            $status = 'transitioning';
        }
        
        $betsRemaining = 0;
        $daysRemaining = 0;
        
        if ($profile->first_bet_at) {
            $betsLimit = $this->getProtectionBets();
            $daysLimit = $this->getProtectionDays();
            
            $betsRemaining = max(0, ($betsLimit + self::TRANSITION_BETS) - $profile->bet_count);
            $daysRemaining = max(0, $daysLimit - $profile->first_bet_at->diffInDays(now()));
        }
        
        return [
            'status' => $status,
            'multiplier' => round($multiplier, 3),
            'boost_multiplier' => $boostMultiplier,
            'is_protected' => $multiplier > 1.0,
            'is_fully_protected' => $multiplier >= $boostMultiplier,
            'is_transitioning' => $status === 'transitioning',
            'bets_remaining' => $betsRemaining,
            'days_remaining' => $daysRemaining,
            'transition_bets' => self::TRANSITION_BETS,
        ];
    }
    
    /**
     * Calculate remaining protection percentage.
     * 
     * @param UserLuckProfile $profile
     * @return float Percentage (0.0 to 100.0)
     */
    public function getRemainingProtectionPercent(UserLuckProfile $profile): float
    {
        $multiplier = $this->getMultiplier($profile);
        $boostMultiplier = $this->getBoostMultiplier();
        
        if ($multiplier <= 1.0) {
            return 0.0;
        }
        
        // Calculate what percentage of the boost is still active
        $boostRange = $boostMultiplier - 1.0;
        $currentBoost = $multiplier - 1.0;
        
        return ($currentBoost / $boostRange) * 100;
    }
    
    /**
     * Quadratic ease-out function for smooth transition.
     * 
     * @param float $t Progress (0.0 to 1.0)
     * @return float Eased value (0.0 to 1.0)
     */
    private function easeOutQuad(float $t): float
    {
        return $t * (2 - $t);
    }
    
    /**
     * Get protection days from settings.
     * 
     * @return int
     */
    private function getProtectionDays(): int
    {
        return (int) FairLuckSetting::getByKey('beginner_protection_days', self::DEFAULT_PROTECTION_DAYS);
    }
    
    /**
     * Get protection bets limit from settings.
     * 
     * @return int
     */
    private function getProtectionBets(): int
    {
        return (int) FairLuckSetting::getByKey('beginner_protection_bets', self::DEFAULT_PROTECTION_BETS);
    }
    
    /**
     * Get max profit rate from settings.
     * 
     * @return float
     */
    private function getMaxProfitRate(): float
    {
        return (float) FairLuckSetting::getByKey('beginner_max_profit_rate', self::DEFAULT_MAX_PROFIT_RATE);
    }
    
    /**
     * Get boost multiplier from settings.
     * 
     * @return float
     */
    private function getBoostMultiplier(): float
    {
        return (float) FairLuckSetting::getByKey('beginner_boost_multiplier', self::DEFAULT_BOOST_MULTIPLIER);
    }
}
