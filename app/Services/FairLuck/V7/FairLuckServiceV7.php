<?php

namespace App\Services\FairLuck\V7;

use App\Models\CoreWallet;
use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Support\Facades\DB;

/**
 * FairLuckServiceV7: Single-step weighted selection engine.
 *
 * Money flow (all integer arithmetic):
 *   1. APP_FEE = round(B × appFeeRate) → app_wallet (credited ONCE)
 *   2. NET_BET = B - APP_FEE → lucky_wallet
 *   3. MultiplierTable selects M (0 = no win)
 *   4. TOTAL_PAYOUT = NET_BET × M
 *   5. RECEIVER = round(TOTAL_PAYOUT × receiverRate)
 *   6. HOST = round(TOTAL_PAYOUT × ownerRate)
 *   7. SENDER = TOTAL_PAYOUT - RECEIVER - HOST
 *   8. lucky_wallet -= TOTAL_PAYOUT
 *
 * All rates are admin-configurable via FairLuckSetting.
 * Octane-safe: Register as bind() not singleton().
 */
class FairLuckServiceV7
{
    public function __construct(
        private UserRTPTracker $rtpTracker,
        private MultiplierTable $multiplierTable,
        private PoolManager $poolManager,
        private ProfileManager $profileManager,
    ) {}

    /**
     * Process a single bet.
     *
     * @param int $betAmount GROSS bet amount (before any fee deduction).
     *                       The service handles all fee splitting internally.
     */
    public function processBet(
        User $user,
        Gift $gift,
        int $betAmount,
        float $unitPrice = 0,
        ?int $roomId = null,
        $receiverId = null,
        int $callerAppFee = 0,
        int $callerReceiverFee = 0,
        int $senderBalanceBefore = 0,
        int $senderBalanceAfter = 0,
        ?int $currentLossStreak = null
    ): object {
        return DB::transaction(function () use (
            $user, $gift, $betAmount, $roomId,
            $senderBalanceBefore, $senderBalanceAfter, $currentLossStreak
        ) {
            // Read admin-configurable rates
            // Read app fee from admin panel key first, fall back to V7 key
            $appFeeRate = (float) FairLuckSetting::getByKey('fair_luck_app_fee_rate',
                FairLuckSetting::getByKey('V7_app_fee_rate', MultiplierTable::DEFAULT_APP_FEE_RATE)
            );
            $receiverRate = (float) FairLuckSetting::getByKey('fair_luck_receiver_fee_rate', 0.10);
            $ownerRate = (float) FairLuckSetting::getByKey('fair_luck_owner_fee_rate', 0.10);
            $targetRTP = (float) FairLuckSetting::getByKey('V7_target_rtp', 0.99);

            // STEP 1: Compute fees (integer arithmetic)
            $APP_FEE = (int) round($betAmount * $appFeeRate);
            $NET_BET = $betAmount - $APP_FEE;

            // STEP 2: Credit app_wallet with APP_FEE (ONCE)
            if ($APP_FEE > 0) {
                CoreWallet::where('name', 'app_wallet')->increment('coins', $APP_FEE);
            }

            // STEP 3: Credit lucky_wallet with NET_BET
            $this->poolManager->creditBet($NET_BET);

            // STEP 4: Get current lucky_wallet balance (after credit)
            $luckyBalanceBefore = $this->poolManager->getBalance();

            // STEP 5: Get user RTP stats
            $stats = $this->rtpTracker->getStats($user->id);

            // STEP 6: Single-step weighted selection
            // Read loss streak from Redis if not provided by caller
            $lossStreak = $currentLossStreak ?? (int) ($stats->consecutive_losses ?? 0);

            $selection = $this->multiplierTable->select(
                $luckyBalanceBefore,
                (int) $stats->total_spent,
                (int) $stats->total_received,
                $betAmount,
                $lossStreak
            );

            $multiplier = $selection['multiplier'];
            $isWinner = ($multiplier > 0);

            // STEP 7: Compute payouts using admin-configurable rates
            $TOTAL_PAYOUT = 0;
            $RECEIVER_PAYOUT = 0;
            $HOST_PAYOUT = 0;
            $SENDER_PAYOUT = 0;

            if ($isWinner) {
                $TOTAL_PAYOUT = $NET_BET * $multiplier;
                $RECEIVER_PAYOUT = (int) round($TOTAL_PAYOUT * $receiverRate);
                $HOST_PAYOUT = (int) round($TOTAL_PAYOUT * $ownerRate);
                $SENDER_PAYOUT = $TOTAL_PAYOUT - $RECEIVER_PAYOUT - $HOST_PAYOUT;

                // STEP 8: Debit lucky_wallet (atomic via Lua script)
                $paid = $this->poolManager->debitPayout($TOTAL_PAYOUT);
                if (!$paid) {
                    $multiplier = 0;
                    $isWinner = false;
                    $TOTAL_PAYOUT = $SENDER_PAYOUT = $RECEIVER_PAYOUT = $HOST_PAYOUT = 0;
                }
            }

            $luckyBalanceAfter = $this->poolManager->getBalance();

            // STEP 9: Track RTP (integer-based)
            $this->rtpTracker->recordBet(
                $user->id,
                $betAmount,
                $SENDER_PAYOUT,
                $isWinner
            );

            // STEP 10: Update legacy profile
            $profile = $this->profileManager->getProfile($user->id);
            $netProfit = $isWinner ? ($SENDER_PAYOUT - $betAmount) : -$betAmount;

            $newStats = $this->rtpTracker->getStats($user->id);
            $newRTP = $newStats->total_spent > 0
                ? $newStats->total_received / $newStats->total_spent
                : 0.0;

            $this->profileManager->updateStats(
                $profile,
                $betAmount,
                $netProfit,
                $isWinner,
                $targetRTP - $newRTP
            );

            // STEP 11: Log transaction
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'app_fee' => $APP_FEE,
                'receiver_fee' => $RECEIVER_PAYOUT,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $netProfit,
                'deviation_before' => $targetRTP - ($stats->total_spent > 0 ? $stats->total_received / $stats->total_spent : 0),
                'calculated_probability' => 0,
                'is_beginner_protected' => false,
                'protection_multiplier' => 1.0,
                'room_id' => $roomId,
                'sender_balance_before' => $senderBalanceBefore,
                'sender_balance_after' => $senderBalanceAfter,
                'wallets_before' => ['lucky_wallet' => $luckyBalanceBefore],
                'wallets_after' => ['lucky_wallet' => $luckyBalanceAfter],
            ]);

            return (object) [
                'isWinner'         => $isWinner,
                'multiplier'       => $multiplier,
                'profitAmount'     => $SENDER_PAYOUT,
                'receiverPayout'   => $RECEIVER_PAYOUT,
                'hostPayout'       => $HOST_PAYOUT,
                'totalPayout'      => $TOTAL_PAYOUT,
                'appFee'           => $APP_FEE,
                'netBet'           => $NET_BET,
                'walletFactor'     => $selection['walletFactor'],
                'rtpFactor'        => $selection['rtpFactor'],
                'walletZone'       => $selection['walletZone'],
                'jackpotGateFired' => $selection['jackpotGateFired'],
                'newDeviation'     => $targetRTP - $newRTP,
                'actualRTP'        => $newRTP,
                'targetRTP'        => $targetRTP,
                'wallets_before'   => ['lucky_wallet' => $luckyBalanceBefore],
                'wallets_after'    => ['lucky_wallet' => $luckyBalanceAfter],
                'pool_health'      => ['status' => $selection['walletZone']],
            ];
        });
    }
}
