<?php

namespace App\Services\FairLuck;

use App\Models\User;
use App\Models\Gift;
use App\Models\FairLuckTransaction;
use Illuminate\Support\Facades\DB;

class FairLuckServiceSmartManaged
{
    protected $profileManager;
    protected $deviationCalculator;

    public function __construct(
        ProfileManager $profileManager,
        DeviationCalculator $deviationCalculator
    ) {
        $this->profileManager = $profileManager;
        $this->deviationCalculator = $deviationCalculator;
    }

    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId) {
            $profile = $this->profileManager->getProfile($user->id);
            $deviation = (float) $profile->current_deviation;
            
            // 1. Playtime Maximizer (Target 99% RTP / 1% App Profit)
            $targetRTP = 0.99;
            $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
            $weights = [800, 400, 150, 40, 10, 5, 2, 1];
            $expectedMultiplier = 0;
            $totalWeight = array_sum($weights);
            foreach ($multipliers as $i => $m) {
                $expectedMultiplier += $m * ($weights[$i] / $totalWeight);
            }
            $idealProb = $targetRTP / $expectedMultiplier;

            // 2. System Mood (Chaos Factor)
            $chaosFactor = mt_rand(95, 105) / 100; // Reduced chaos for more stability (longer playtime)

            // 3. Read Base Probability from Gift Model and Blend
            $adminProb = (float) ($gift->luckyGift?->win_probability ?? ($idealProb * 100)) / 100;
            $baseProb = ($adminProb * 0.2) + ($idealProb * 0.8);

            // 4. Adaptive Adjustment based on Deviation
            // To maximize playtime, we make adjustments very gentle.
            $adjustedProb = $baseProb;
            if ($deviation > 0.10) {
                $adjustedProb *= 0.8; // Gentle reduction
            } elseif ($deviation < -0.10) {
                $adjustedProb *= 1.2; // Gentle boost
            }
            
            $adjustedProb *= $chaosFactor;

            // 4. Win/Loss
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $adjustedProb;

            // 5. Multiplier Selection (Allow 1000x)
            $multiplier = 0;
            if ($isWinner) {
                $multiplier = $this->smartManagedMultiplier($deviation, $chaosFactor);
            }

            $profitAmount = $isWinner ? (($multiplier * $betAmount) - $betAmount) : -$betAmount;
            
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);

            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviation,
                'calculated_probability' => $adjustedProb,
                'room_id' => $roomId,
            ]);

            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $profitAmount,
                'newDeviation' => $newDeviation,
                'mood' => $deviation > 0.05 ? 'Recovery' : ($deviation < -0.10 ? 'Generous' : 'Stable')
            ];
        });
    }

    private function smartManagedMultiplier(float $deviation, float $chaos): int
    {
        $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
        $weights = [500, 300, 100, 50, 20, 10, 5, 2];

        if ($deviation > 0.1) {
            // Tighten but still allow rare high wins below 100x
            $weights = [600, 300, 50, 10, 2, 0, 0, 0];
        } elseif ($deviation < -0.15) {
            // Deficit state: Increase weight of 500x and 1000x
            $weights[6] *= 5; // 500x
            $weights[7] *= 5; // 1000x
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) return $values[0];
        $random = rand(1, (int)$totalWeight);
        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) return $value;
        }
        return $values[0];
    }
}
