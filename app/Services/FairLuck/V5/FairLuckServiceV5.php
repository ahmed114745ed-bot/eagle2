<?php

namespace App\Services\FairLuck\V5;

use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\ProfileManager;
use App\Services\FairLuck\WalletManager;
use App\Services\FairLuck\HighMultiplierLedger;
use App\Services\FairLuck\LossLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * FairLuck Service V5 - Main Orchestrator
 * 
 * Orchestrates the 5 pillars of the FairLuck V5 system:
 * - Pillar A: GlobalStabilityManager (Platform-wide RTP monitoring)
 * - Pillar B: MultiplierSelector (Weighted scaling with curve logic)
 * - Pillar C: DeviationCalculator (Dynamic profit extraction)
 * - Pillar D: BeginnerProtection (Gradual transition)
 * - Pillar E: ProbabilityEngine (Holistic individual RTP)
 */
class FairLuckServiceV5
{
    private GlobalStabilityManager $stabilityManager;
    private MultiplierSelector $multiplierSelector;
    private DeviationCalculator $deviationCalculator;
    private BeginnerProtection $beginnerProtection;
    private ProbabilityEngine $probabilityEngine;
    private ProfileManager $profileManager;
    private HighMultiplierLedger $highMultiplierLedger;
    private LossLedger $lossLedger;
    
    public function __construct(
        GlobalStabilityManager $stabilityManager,
        MultiplierSelector $multiplierSelector,
        DeviationCalculator $deviationCalculator,
        BeginnerProtection $beginnerProtection,
        ProbabilityEngine $probabilityEngine,
        ProfileManager $profileManager,
        HighMultiplierLedger $highMultiplierLedger,
        LossLedger $lossLedger
    ) {
        $this->stabilityManager = $stabilityManager;
        $this->multiplierSelector = $multiplierSelector;
        $this->deviationCalculator = $deviationCalculator;
        $this->beginnerProtection = $beginnerProtection;
        $this->probabilityEngine = $probabilityEngine;
        $this->profileManager = $profileManager;
        $this->highMultiplierLedger = $highMultiplierLedger;
        $this->lossLedger = $lossLedger;
    }
    
    /**
     * Process a bet using the V5 system.
     * 
     * @param User $user
     * @param Gift $gift
     * @param float $betAmount
     * @param int|null $roomId
     * @param int|null $receiverId
     * @param float $appFee
     * @param float $receiverFee
     * @param float $senderBalanceBefore
     * @param float $senderBalanceAfter
     * @return object
     */
    public function processBet(
        User $user,
        Gift $gift,
        float $betAmount,
        ?int $roomId = null,
        $receiverId = null,
        float $appFee = 0,
        float $receiverFee = 0,
        float $senderBalanceBefore = 0,
        float $senderBalanceAfter = 0
    ): object {
        return DB::transaction(function () use (
            $user, $gift, $betAmount, $roomId, $appFee, $receiverFee,
            $senderBalanceBefore, $senderBalanceAfter
        ) {
            $totalAmount = $betAmount + $appFee + $receiverFee;
            
            // Get user profile with lifetime data (Pillar E - Holistic RTP)
            $profile = $this->profileManager->getProfile($user->id);
            $deviation = (float) $profile->current_deviation;
            
            // Get Redis state
            $lossKey = "fairluck_loss_streak_{$user->id}";
            $consecutiveLosses = (int) (Redis::get($lossKey) ?? 0);
            $jackpotPity = 0; // Mechanics removed
            $contributionBank = 0; // Mechanics removed
            $isDrainLocked = false; // System lock removed - Open Mathematical Model
            
            // 1. حساب النسب وتوزيعها (Gaming Company Logic)
            $appPercent = getGiftPercentage('app_wallet_lucky_gift') / 10;
            $roomPercent = getGiftPercentage('owner_lucky_gift') / 10;
            $hostPercent = getGiftPercentage('host_lucky_gift') / 10;

            $appAmount = ($betAmount * $appPercent) / 100;
            $roomAmount = ($betAmount * $roomPercent) / 100;
            $hostAmount = ($betAmount * $hostPercent) / 100;

            // المبلغ الصافي الذي يغذي المحفظة ويحسب عليه الـ RTP
            $netBet = $betAmount - ($appAmount + $roomAmount + $hostAmount);

            // 2. حساب الفجوة في الـ RTP (RTP Gap)
            $actualRTP = $profile->total_bets > 0 ? ($profile->total_profit / $profile->total_bets) : 0;
            $targetRTP = (float) \App\Models\FairLuckSetting::getByKey('target_rtp', 0.95);
            $rtpGap = $targetRTP - $actualRTP;
            
            // betImpact = rtpGap / netBet
            $betImpact = $rtpGap / ($netBet > 0 ? $netBet : 1);
            
            // 3. توزيع الرهان الصافي للمحفظة (قاعدة الـ 94%)
            $this->distributeBetAmount($netBet);
            
            // Wallet balances before (Pillar A - Single Liquidity Pool)
            $walletsBefore = [
                'unified_vault' => FairLuckWallet::getVaultBalance(),
                'global_vault' => 0,
                'jackpot_wallet' => 0,
                'medium_wallet' => 0,
            ];
            
            // Get global adjustment (Pillar A - Global Stability)
            $globalAdjustment = $this->stabilityManager->getGlobalAdjustment();
            
            // Get beginner protection multiplier (Pillar D - Gradual Transition)
            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $isBeginner = $this->beginnerProtection->isUnderProtection($profile);
            
            // Calculate final probability (Pillar E - Probability Engine - Gaming System)
            $finalProbability = $this->probabilityEngine->calculate(
                $betImpact,
                $profile->current_deviation,
                $protectionMultiplier,
                (float) $user->di,
                $netBet,
                $globalAdjustment,
                [
                    'actual_rtp' => $actualRTP, 
                    'is_drain_locked' => false,
                    'total_bets' => $profile->total_bets
                ]
            );
            
            // Apply loss streak boost
            $finalProbability = $this->probabilityEngine->applyLossStreakBoost(
                $finalProbability,
                $consecutiveLosses
            );
            
            // Probability is now calculated in the engine using betImpact and context
            // Final adjustments like loss streak can still be applied for user satisfaction
            
            // Determine if user wins
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;
            
            // Force win after 15+ consecutive losses
            $forceWin = $consecutiveLosses >= 15;
            if ($forceWin) {
                $isWinner = true;
            }
            
            // Select multiplier if winner (Pillar B - Weighted Scaling)
            $multiplier = 0;
            if ($isWinner) {
                $multiplier = $this->selectMultiplier(
                    $deviation,
                    $globalAdjustment,
                    $consecutiveLosses,
                    $jackpotPity,
                    $contributionBank,
                    $isDrainLocked,
                    $isBeginner,
                    $betAmount,
                    $user->di
                );
                
                // Validate and process payout (With Downshifting)
                $payoutResult = $this->processWinPayout($multiplier, $betAmount, $user->id);
                if (!$payoutResult['success']) {
                    $isWinner = false;
                    $multiplier = 0;
                } else {
                    // Update multiplier in case it was downshifted
                    $multiplier = $payoutResult['multiplier'] ?? $multiplier;
                }
            }
            
            // Update Redis state
            $this->updateRedisState(
                $user->id,
                $isWinner,
                $multiplier,
                $totalAmount,
                $consecutiveLosses,
                $jackpotPity,
                $contributionBank
            );
            
            // Calculate profit (System View: Payout - NetBet)
            $payoutAmount = $isWinner ? ($multiplier * $betAmount) : 0;
            $profitAmount = $payoutAmount - $netBet;
            
            // Calculate new deviation based on Net Bet (Standard Company Model)
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $netBet,
                $profile->total_profit + $profitAmount
            );
            
            // Update historical profile using ProfileManager (to ensure bet_count, win_count, etc.)
            $this->profileManager->updateStats(
                $profile,
                $netBet,
                $profitAmount,
                $isWinner,
                $newDeviation
            );
            
            // Record outcome in ledgers
            $this->highMultiplierLedger->recordOutcome(
                $user->id,
                $totalAmount,
                $profitAmount,
                $isWinner,
                $multiplier
            );
            
            // Wallet balances after
            $walletsAfter = [
                'unified_vault' => FairLuckWallet::getVaultBalance(),
                'global_vault' => 0,
                'jackpot_wallet' => 0,
                'medium_wallet' => 0,
            ];
            
            // Create transaction record
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $totalAmount,
                'app_fee' => $appFee,
                'receiver_fee' => $receiverFee,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviation,
                'calculated_probability' => $finalProbability,
                'is_beginner_protected' => $protectionMultiplier > 1,
                'protection_multiplier' => $protectionMultiplier,
                'room_id' => $roomId,
                'sender_balance_before' => $senderBalanceBefore,
                'sender_balance_after' => $senderBalanceAfter,
                'wallets_before' => $walletsBefore,
                'wallets_after' => $walletsAfter,
            ]);
            
            // Determine mood
            $mood = $this->probabilityEngine->determineMood($finalProbability, $deviation);
            
            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $profitAmount,
                'newDeviation' => $newDeviation,
                'mood' => $mood,
                'globalAdjustment' => $globalAdjustment,
                'protectionMultiplier' => $protectionMultiplier,
                'finalProbability' => $finalProbability,
                'wallets_before' => $walletsBefore,
                'wallets_after' => $walletsAfter,
            ];
        });
    }
    
    /**
     * Calculate target RTP based on user state.
     */
    private function calculateTargetRTP(float $deviation, bool $isDrainLocked, int $jackpotPity): float
    {
        $baseRTP = 0.80;
        
        // Adjust based on deviation
        if ($deviation >= 0.05) {
            $baseRTP = 0.40;
        } elseif ($deviation >= 0) {
            $baseRTP = 0.70;
        } elseif ($deviation < -0.15) {
            $baseRTP = 0.92;
        }
        
        // Adjust based on jackpot pity
        if ($jackpotPity > 800) {
            $baseRTP = max($baseRTP, 0.70);
        }
        
        // Drain lock reduces RTP
        if ($isDrainLocked) {
            $baseRTP = min($baseRTP, 0.30);
        }
        
        // Add chaos factor
        $chaosFactor = mt_rand(85, 115) / 100;
        
        return $baseRTP * $chaosFactor;
    }
    
    /**
     * Select multiplier using V5 weighted scaling.
     */
    private function selectMultiplier(
        float $deviation,
        float $globalAdjustment,
        int $consecutiveLosses,
        int $jackpotPity,
        int $contributionBank,
        bool $isDrainLocked,
        bool $isBeginner,
        float $betAmount,
        float $userBalance
    ): int {
        // Build context for multiplier selection
        $context = [
            'consecutive_losses' => $consecutiveLosses,
            'pity_count' => $jackpotPity,
            'contribution_bank' => $contributionBank,
            'is_drain_locked' => $isDrainLocked,
            'is_beginner' => $isBeginner,
            'bet_amount' => $betAmount,
            'user_balance' => $userBalance,
            'force_mini_wins' => $userBalance <= ($betAmount * 20),
            'force_jackpot' => $jackpotPity > 1000 && !$isDrainLocked,
        ];
        
        return $this->multiplierSelector->selectMultiplier(
            $deviation,
            $globalAdjustment,
            $context
        );
    }
    
    /**
     * Process win payout from the Unified Vault with Downshifting capability.
     */
    private function processWinPayout(int &$multiplier, float $betAmount, int $userId): array
    {
        while ($multiplier > 0) {
            $payout = (int) round(max(0, $multiplier * $betAmount));
            
            if (FairLuckWallet::decreaseVault($payout, "Win payout ({$multiplier}x)", $userId)) {
                return ['success' => true, 'source' => 'unified_vault', 'multiplier' => $multiplier];
            }
            
            // Downshift: Try the next lower multiplier instead of failing
            $multiplier = $this->multiplierSelector->getNextLowerMultiplier($multiplier);
        }
        
        return ['success' => false, 'reason' => 'insufficient_liquidity'];
    }
    
    /**
     * Distribute net bet amount to the Unified Vault (94% Rule).
     */
    private function distributeBetAmount(float $netBet): void
    {
        // 94% of netBet is directed to prize funds (Unified Vault)
        $poolAmount = (int) round($netBet * 0.94);
        
        FairLuckWallet::increaseVault($poolAmount, "Net bet contribution to Unified Vault (94%)");
    }
    
    /**
     * Update Redis state after bet.
     */
    private function updateRedisState(
        int $userId,
        bool $isWinner,
        int $multiplier,
        float $totalAmount,
        int $consecutiveLosses,
        int $jackpotPity,
        int $contributionBank
    ): void {
        $lossKey = "fairluck_loss_streak_{$userId}";
        $jackpotKey = "fairluck_jackpot_pity_{$userId}";
        $contributionKey = "fairluck_contribution_bank_{$userId}";
        $drainLockKey = "fairluck_drain_lock_{$userId}";
        
        if ($isWinner) {
            Redis::del($lossKey);
        } else {
            Redis::incr($lossKey);
            Redis::expire($lossKey, 3600);
            
            // Track global loss pool for statistics only
            $this->lossLedger->addToGlobalPool((int) round($totalAmount));
        }
    }
    
    // Unified wallet helpers (Legacy wrappers removed)
    
    /**
     * Get system metrics for monitoring.
     */
    public function getSystemMetrics(): array
    {
        return [
            'stability' => $this->stabilityManager->getStabilityMetrics(),
            'profit' => $this->deviationCalculator->getProfitMetrics(),
            'wallets' => [
                'unified_vault' => FairLuckWallet::getVaultBalance()
            ],
        ];
    }
}
