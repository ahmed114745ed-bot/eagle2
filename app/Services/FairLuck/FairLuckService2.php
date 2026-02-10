<?php

namespace App\Services\FairLuck;

use App\Models\CoreWallet;
use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * FairLuckService2: Dual Algorithm Implementation
 * 
 * Algorithm 1 (Macro): Controller that allows high multipliers/sessions.
 * Algorithm 2 (Micro): Controller that handles recovery and app profit via small random cycles.
 */
class FairLuckService2
{
    public function __construct(
        private ProfileManager $profileManager,
        private DeviationCalculator $deviationCalculator,
        private BeginnerProtection $beginnerProtection,
        private ProbabilityEngine $probabilityEngine,
        private MultiplierSelector $multiplierSelector,
        private DualAlgorithmEngine $dualEngine
    ) {}

    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId) {
            // 1. Load User Profile
            $profile = $this->profileManager->getProfile($user->id);
            $deviationBefore = (float) $profile->current_deviation;

            // 2. Dual Algorithm Context
            $macroAllowed = $this->dualEngine->isMacroAllowed($profile);
            $microAdjust = $this->dualEngine->getMicroAdjustment($profile);

            // 3. Probability Calculation
            $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
            $targetRTP = 1.0 - $targetLossRate;
            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            $baseProb = $targetRTP / $expectedMultiplier;

            // Apply Micro Adjustment to Probability if in recovery
            $adjustedBaseProb = $baseProb * $microAdjust['prob_multiplier'];

            // Beginner Protection (Only if Macro is allowed or Not in Recovery)
            $protectionMultiplier = 1.0;
            if (!$profile->is_in_recovery) {
                $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            }
            
            $probability = $this->probabilityEngine->calculate($adjustedBaseProb, $deviationBefore, $protectionMultiplier);

            // 4. Determine Win/Loss
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $probability;

            // 5. App Wallet Security
            $appWallet = CoreWallet::where('name', 'app_wallet')->first();
            
            $multiplier = 0;
            $profitAmount = 0;

            if ($isWinner) {
                if ($microAdjust['force_low_multipliers']) {
                    // Recovery weighted multipliers: mostly small, rare medium (50x) as bait
                    $rand = mt_rand(1, 100);
                    if ($rand <= 5) { // 5% chance for "Bait" win
                        $multiplier = 50;
                    } elseif ($rand <= 25) { // 20% chance for mid-low
                        $multiplier = 20;
                    } elseif ($rand <= 60) { // 35% chance for low
                        $multiplier = 10;
                    } else { // 40% chance for minimum win
                        $multiplier = 5;
                    }
                } else {
                    $multiplier = $this->multiplierSelector->select($deviationBefore);
                }

                $winTotalAmount = $multiplier * $betAmount;

                // Budget Check
                if ($appWallet && $appWallet->coins < $winTotalAmount) {
                    $isWinner = false;
                    $multiplier = 0;
                }
            }

            if ($isWinner) {
                $profitAmount = ($multiplier * $betAmount) - $betAmount;
            } else {
                $profitAmount = -$betAmount;
            }

            // 7. Calculate New Deviation
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            // 8. Update Stats & Dual Engine State
            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);
            
            // Update Dual Engine State AFTER saving profile stats so it sees the new total_profit
            $this->dualEngine->updateState($profile, $betAmount, $isWinner ? ($multiplier * $betAmount) : 0, $isWinner);
            
            // Persist the dual engine flags (is_in_recovery, recovery_target_profit)
            $profile->save();

            // 9. Log Transaction
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviationBefore,
                'calculated_probability' => $probability,
                'is_beginner_protected' => (!$profile->is_in_recovery && $protectionMultiplier > 1),
                'protection_multiplier' => $protectionMultiplier,
                'room_id' => $roomId,
            ]);

            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $profitAmount,
                'newDeviation' => $newDeviation,
                'isRecovery' => $profile->is_in_recovery
            ];
        });
    }
}
