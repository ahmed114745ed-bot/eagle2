<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;
use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * FairLuckService3: The Intelligent Hybrid System (Neural-inspired Adaptive Logic)
 * 
 * Instead of switching between two modes, this service uses a continuous "Luck Mood"
 * that adapts to the user's every move, ensuring:
 * 1. Unpredictability (No fixed rhythm).
 * 2. Guaranteed App Profit (Long-term 1% target).
 * 3. User Engagement (Bait wins and "near-miss" simulation).
 */
class FairLuckService3
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
            // 1. Load context
            $profile = $this->profileManager->getProfile($user->id);
            $deviation = (float) $profile->current_deviation; // Positive = User winning, Negative = User losing
            
            // 2. Calculate "System Mood" (Chaos Factor)
            // This ensures every bet has a slightly different math profile to prevent rhythm detection.
            $chaosFactor = mt_rand(85, 115) / 100; // ±15% variation in math

            // 3. Dynamic RTP Adjustment
            $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
            $targetRTP = (1.0 - $targetLossRate);
            
            // Adaptive Base Probability
            // Dynamic Equilibrium: Use the targetRTP (usually 0.99 for 1% profit)
            $localTargetRTP = $targetRTP; 
            
            if ($deviation > 0.01) {
                // User is in profit, gently slow down to 97%
                $localTargetRTP = 0.97;
            } elseif ($deviation < -0.02) {
                // Safety Net: Immediate boost to force a recovery and keep user engaged
                $localTargetRTP = 1.40 * $chaosFactor;
            }

            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            $baseProb = ($localTargetRTP / $expectedMultiplier);

            // 4. Intelligence Layer: Win/Loss Calculation
            // We combine beginner protection and system mood
            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $finalProbability = $this->probabilityEngine->calculate($baseProb, $deviation, $protectionMultiplier);
            
            // Add extra randomness layer
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;

            // 5. Intelligent Multiplier Selection (The Bait Logic)
            $multiplier = 0;
            if ($isWinner) {
                // Instead of a simple choice, we shift weights dynamically based on user's current session "luck"
                $multiplier = $this->smartMultiplierSelect($deviation, $chaosFactor);
            }

            // 6. Update Stats
            $profitAmount = $isWinner ? (($multiplier * $betAmount) - $betAmount) : -$betAmount;
            
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);

            // 7. Log and Return
            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviation,
                'calculated_probability' => $finalProbability,
                'is_beginner_protected' => ($protectionMultiplier > 1),
                'protection_multiplier' => $protectionMultiplier,
                'room_id' => $roomId,
            ]);

            return (object) [
                'isWinner' => $isWinner,
                'multiplier' => $multiplier,
                'profitAmount' => $profitAmount,
                'newDeviation' => $newDeviation,
                'mood' => $localTargetRTP > 1 ? 'Generous' : ($localTargetRTP < 0.95 ? 'Recovery' : 'Stable')
            ];
        });
    }

    /**
     * Smart weight-based selection to keep user in suspense.
     */
    private function smartMultiplierSelect(float $deviation, float $chaos): int
    {
        $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
        $weights = [];

        foreach ($multipliers as $m) {
            $baseWeight = $this->getNaturalWeight($m);
            
            if ($deviation > 0.02) { 
                if ($m > 20) {
                    $weights[] = 0;
                } elseif ($m > 5) {
                    $weights[] = (int)($baseWeight * 0.1); 
                } else {
                    $weights[] = $baseWeight * 3; 
                }
            } elseif ($deviation < -0.15) {
                if ($m >= 250) $weights[] = $baseWeight * 5 * $chaos;
                else $weights[] = $baseWeight;
            } else {
                $weights[] = $baseWeight * $chaos;
            }
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function getNaturalWeight(int $multiplier): int
    {
        return match ($multiplier) {
            5 => 450, 10 => 250, 20 => 120, 50 => 60, 
            100 => 30, 250 => 15, 500 => 10, 1000 => 5,
            default => 1,
        };
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
