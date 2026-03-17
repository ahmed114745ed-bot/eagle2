<?php

namespace App\Services\FairLuck\V6;

use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Support\Facades\DB;

class FairLuckServiceV6
{
    public function __construct(
        private UserRTPTracker $rtpTracker,
        private ProbabilityCalculator $probabilityCalculator,
        private RewardSelector $rewardSelector,
        private PoolManager $poolManager,
        private ProfileManager $profileManager,
    ) {}

    /**
     * Process a single bet using the V6 RTP-based algorithm.
     *
     * Key differences from V3:
     * 1. RTP = totalReceived / totalSpent (per user, lifetime) - same as game company
     * 2. Probability based on RTP gap relative to bet amount (not consecutive losses)
     * 3. Unified pool - never cancels a win, always falls back to lower multiplier
     * 4. No drain lock - RTP naturally reduces probability after big wins
     * 5. Bet amount matters: small bets relative to deficit = more boost
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
            $totalAmount = $betAmount + $appFee + $receiverFee;

            // 1. Get user's RTP stats from Redis
            $stats = $this->rtpTracker->getStats($user->id);
            $actualRTP = $stats->total_spent > 0
                ? $stats->total_received / $stats->total_spent
                : 0.0;

            $targetRTP = (float) FairLuckSetting::getByKey('v6_target_rtp', 0.97);
            $rtpGap = $targetRTP - $actualRTP;

            // 2. Get pool balances (before)
            $walletsBefore = $this->poolManager->getWalletBalances();

            // 3. Distribute bet contribution to pool
            $this->poolManager->distributeBet($totalAmount);

            // 4. Calculate win probability based on RTP gap
            // RTP is tracked on totalAmount (what enters pool), so probability uses totalAmount too
            $expectedMultiplier = $this->rewardSelector->getExpectedMultiplier();

            $finalProbability = $this->probabilityCalculator->calculate(
                $actualRTP,
                $targetRTP,
                $stats->total_spent,
                $totalAmount,
                $stats->bet_count,
                $expectedMultiplier
            );

            // 5. Chaos factor (0.90 to 1.10) for unpredictability
            $chaosFactor = mt_rand(90, 110) / 100;
            $finalProbability = max(0.02, min(0.90, $finalProbability * $chaosFactor));

            // 6. Low balance protection
            $userBalance = (int) $user->di;
            if ($userBalance > 0 && $unitPrice > 0 && ($userBalance / $unitPrice) < 15) {
                $finalProbability = max($finalProbability, 0.18);
            }

            // 7. Roll the dice
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;

            $multiplier = 0;
            $payoutAmount = 0;

            if ($isWinner) {
                // 8. Select multiplier based on RTP gap
                $updatedPool = $this->poolManager->getTotalBalance();
                $multiplier = $this->rewardSelector->select(
                    $rtpGap,
                    $totalAmount,
                    $updatedPool,
                    $stats->bet_count
                );

                // 9. Validate pool can afford - NEVER cancel, always fallback to lower
                $multiplier = $this->rewardSelector->validateAndFallback(
                    $multiplier,
                    $totalAmount,
                    $updatedPool
                );

                if ($multiplier > 0) {
                    $payoutAmount = (int) round($multiplier * $totalAmount);

                    // 10. Execute payout from pool (cascading across wallets)
                    $paid = $this->poolManager->payout($payoutAmount, $multiplier, $user->id);

                    if (!$paid) {
                        $multiplier = 0;
                        $isWinner = false;
                        $payoutAmount = 0;
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
                'is_beginner_protected' => ($stats->bet_count < (int) FairLuckSetting::getByKey('v6_new_player_bets', 20)),
                'protection_multiplier' => 1.0,
                'room_id' => $roomId,
                'sender_balance_before' => $senderBalanceBefore,
                'sender_balance_after' => $senderBalanceAfter,
                'wallets_before' => $walletsBefore,
                'wallets_after' => $walletsAfter,
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
            ];
        });
    }
}
