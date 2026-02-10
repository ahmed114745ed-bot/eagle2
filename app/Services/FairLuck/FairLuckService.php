<?php

namespace App\Services\FairLuck;

use App\Models\CoreWallet;
use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FairLuckService
{
    public function __construct(
        private ProfileManager $profileManager,
        private DeviationCalculator $deviationCalculator,
        private BeginnerProtection $beginnerProtection,
        private ProbabilityEngine $probabilityEngine,
        private MultiplierSelector $multiplierSelector
    ) {}

  
    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId) {
            // 1. Load User Profile
            $profile = $this->profileManager->getProfile($user->id);
            $deviationBefore = (float) $profile->current_deviation;

            // 2. Check Beginner Protection
            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $isProtected = $this->beginnerProtection->isUnderProtection($profile);

            // 3. Dynamic Base Probability Calculation (Ignoring Dashboard setting for precise RTP control)
            // Goal: Base Probability * Expected Multiplier = (1 - Target Loss Rate)
            $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
            $targetRTP = 1.0 - $targetLossRate;
            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            
            // The probability required to hit the target RTP
            $baseProb = $targetRTP / $expectedMultiplier;

            // 4. Calculate Win Probability with Deviation Adjustment
            $probability = $this->probabilityEngine->calculate($baseProb, $deviationBefore, $protectionMultiplier);

            // 5. Determine Win/Loss
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $probability;

            // 6. Check App Wallet Budget (Security Layer)
            $appWallet = CoreWallet::where('name', 'app_wallet')->first();
            
            $multiplier = 0;
            $profitAmount = 0;

            if ($isWinner) {
                // 7. Select Multiplier
                $multiplier = $this->multiplierSelector->select($deviationBefore);
                $winTotalAmount = $multiplier * $betAmount;

                // If app wallet can't afford the win, force a loss or 0 win
                /*if ($appWallet && $appWallet->coins < $winTotalAmount) {
                    $isWinner = false;
                    $multiplier = 0;
                }*/
            }

            if ($isWinner) {
                // Profit = (multiplier * bet) - bet
                $profitAmount = ($multiplier * $betAmount) - $betAmount;
            } else {
                // Loss
                $profitAmount = -$betAmount;
            }

            // 8. Calculate New Deviation (based on updated stats)
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            // 9. Update User Profile
            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);

            // 10. Log Transaction
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviationBefore,
                'calculated_probability' => $probability,
                'is_beginner_protected' => $isProtected,
                'protection_multiplier' => $protectionMultiplier,
                'room_id' => $roomId,
            ]);

            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $profitAmount,
                'newDeviation' => $newDeviation
            ];
        });
    }
}
