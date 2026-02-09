<?php

namespace App\Services\FairLuck;

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

            // 3. Get Base Probability from Gift (Dashboard Setting)
            $baseProb = ((int) ($gift->luckyGift?->win_probability ?? 30)) / 100;

            // 4. Calculate Win Probability
            $probability = $this->probabilityEngine->calculate($baseProb, $deviationBefore, $protectionMultiplier);

            // 5. Determine Win/Loss
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $probability;

            $multiplier = 0;
            $profitAmount = 0;

            if ($isWinner) {
                // 6. Select Multiplier
                $multiplier = $this->multiplierSelector->select($deviationBefore);
                // Profit = (multiplier * bet) - bet
                $profitAmount = ($multiplier * $betAmount) - $betAmount;
            } else {
                // Loss
                $profitAmount = -$betAmount;
            }

            // 7. Calculate New Deviation (based on updated stats)
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            // 8. Update User Profile
            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);

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
