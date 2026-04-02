<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Support\Facades\DB;

class FairLuckServiceV7
{
    public function __construct(
        private UserRTPTracker $rtpTracker,
        private ProbabilityCalculator $probabilityCalculator,
        private RewardSelector $rewardSelector,
        private PoolManager $poolManager,
        private ProfileManager $profileManager,
    ) {}

    /**
     * Process a single bet using the V7 RTP-based algorithm.
     *
     * V7 User-First Overhaul:
     * 1. Target RTP = 92% by default (admin configurable 70-99%)
     * 2. Prize SIZE reduced when wallet is low, not win frequency
     * 3. Cooldown disabled by default - users can win big back-to-back
     * 4. Multiplier weights are configurable and flattened
     * 5. Wallet protection operates in USD (project-agnostic)
     * 6. Probability never goes below 60% of normal
     * 7. Unified pool - never cancels a win, always falls back
     */
    public function processBet(
        User $user,
        Gift $gift,
        float $betAmount,
        float $unitPrice,
        ?int $roomId = null,
        $receiverId = null,
        float $appFee = 0,
        float $receiverFee = 0,
        float $senderBalanceBefore = 0,
        float $senderBalanceAfter = 0
    ): object {
        return DB::transaction(function () use (
            $user, $gift, $betAmount, $unitPrice, $roomId, $appFee, $receiverFee,
            $senderBalanceBefore, $senderBalanceAfter
        ) {
            // receiver fee goes directly to receiver (via updateUsers), not into pool
            $totalAmount = $betAmount + $appFee;

            // BANKRUPTCY PROTECTION: Check pool health before processing
            $bankruptcyProtection = app(BankruptcyProtection::class);
            $poolHealth = $bankruptcyProtection->getHealthStatus();
            
            if ($poolHealth['status'] === 'critical') {
                $bankruptcyProtection->logCriticalEvent('BET_PROCESSED_IN_CRITICAL_STATE', [
                    'user_id' => $user->id,
                    'bet_amount' => $totalAmount,
                ]);
            }

            // 1. Get user's RTP stats from Redis
            $stats = $this->rtpTracker->getStats($user->id);
            $actualRTP = $stats->total_spent > 0
                ? $stats->total_received / $stats->total_spent
                : 0.0;

            // V7: Target RTP from settings (default 92%, admin configurable 70-99%)
            $targetRTP = FairLuckSetting::getTargetRTP();
            $rtpGap = $targetRTP - $actualRTP;

            // 2. Get pool balances (before)
            $walletsBefore = $this->poolManager->getWalletBalances();

            // 3. Distribute bet contribution to pool
            $this->poolManager->distributeBet($totalAmount);

            // 4. Get updated pool balance for probability calculation
            $updatedPool = $this->poolManager->getTotalBalance();

            // 5. Calculate win probability based on RTP gap
            // RTP is tracked on totalAmount (what enters pool), so probability uses totalAmount too
            $expectedMultiplier = $this->rewardSelector->getExpectedMultiplier($totalAmount, $updatedPool);

            $finalProbability = $this->probabilityCalculator->calculate(
                $actualRTP,
                $targetRTP,
                $stats->total_spent,
                $totalAmount,
                $stats->bet_count,
                $expectedMultiplier
            );

            // 5. Chaos factor & low balance protection are already applied inside
            // ProbabilityCalculator::calculate() — no need to duplicate here.

            // 6. Roll the dice
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;

            $multiplier = 0;
            $payoutAmount = 0;

            if ($isWinner) {
                // 8. Select multiplier based on RTP gap and wallet health
                $updatedPool = $this->poolManager->getTotalBalance();
                $multiplier = $this->rewardSelector->select(
                    $rtpGap,
                    $totalAmount,
                    $updatedPool,
                    $stats->bet_count
                );

                // 9. Validate pool can afford - NEVER cancel, always fallback to lower
                // This also applies wallet health limits (prize size reduction, not frequency)
                $multiplier = $this->rewardSelector->validateAndFallback(
                    $multiplier,
                    $totalAmount,
                    $updatedPool,
                    $user->id,
                    $stats->bet_count
                );

                if ($multiplier > 0) {
                    // Payout is based on betAmount (what user actually paid per unit * quantity)
                    // This ensures 5x means user gets 5 * betAmount (e.g., 5 * 100 = 500)
                    $payoutAmount = (int) round($multiplier * $betAmount);

                    // BANKRUPTCY PROTECTION: Cap payout based on pool health
                    $safePayout = $bankruptcyProtection->validateAndCapPayout($payoutAmount);
                    if ($safePayout < $payoutAmount) {
                        // Recalculate multiplier based on safe payout
                        $multiplier = $totalAmount > 0 ? (int) round($safePayout / $totalAmount) : 0;
                        $payoutAmount = $safePayout;
                    }

                    // 10. Execute payout from pool (cascading across wallets)
                    $paid = $this->poolManager->payout($payoutAmount, $multiplier, $user->id);

                    if (!$paid) {
                        $multiplier = 0;
                        $isWinner = false;
                        $payoutAmount = 0;
                    } else if ($multiplier > 0 && $isWinner) {
                        // POST-JACKPOT COOLDOWN: Record the jackpot (only if enabled)
                        $cooldown = app(\App\Services\FairLuck\V7\PostJackpotCooldown::class);
                        $cooldown->recordJackpot($user->id, $stats->bet_count, $multiplier);
                    }
                } else {
                    $isWinner = false;
                }
            }

            // 11. Track RTP in Redis (using pool amounts for sustainable RTP)
            // spent = totalAmount (what enters pool), received = payoutAmount (what leaves pool)
            $this->rtpTracker->recordBet(
                $user->id,
                $totalAmount,
                $payoutAmount,
                $isWinner && $multiplier > 0
            );

            // 12. Update legacy profile for compatibility
            $profile = $this->profileManager->getProfile($user->id);
            $netProfit = $isWinner ? $payoutAmount : -$totalAmount;

            $newStats = $this->rtpTracker->getStats($user->id);
            $newRTP = $newStats->total_spent > 0
                ? $newStats->total_received / $newStats->total_spent
                : 0.0;
            $newDeviation = $targetRTP - $newRTP;

            $this->profileManager->updateStats(
                $profile,
                $totalAmount,
                $netProfit,
                $isWinner,
                $newDeviation
            );

            // 13. Wallet balances after
            $walletsAfter = $this->poolManager->getWalletBalances();

            // 14. Log transaction
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $totalAmount,
                'app_fee' => $appFee,
                'receiver_fee' => $receiverFee,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $netProfit,
                'deviation_before' => $rtpGap,
                'calculated_probability' => $finalProbability,
                'is_beginner_protected' => ($stats->bet_count < FairLuckSetting::getByKey('V7_new_player_bets', 20)),
                'protection_multiplier' => 1.0,
                'room_id' => $roomId,
                'sender_balance_before' => $senderBalanceBefore,
                'sender_balance_after' => $senderBalanceAfter,
                'wallets_before' => $walletsBefore,
                'wallets_after' => $walletsAfter,
                'pool_health_status' => $poolHealth['status'],
            ]);

            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $payoutAmount,
                'newDeviation' => $newDeviation,
                'actualRTP' => $newRTP,
                'targetRTP' => $targetRTP,
                'wallets_before' => $walletsBefore,
                'wallets_after' => $walletsAfter,
                'pool_health' => $poolHealth,
            ];
        });
    }
}
