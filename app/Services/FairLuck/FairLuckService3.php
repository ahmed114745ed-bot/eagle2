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
            // Extreme Equilibrium: Target 99.5% RTP (0.5% App Profit)
            $localTargetRTP = 0.995; 
            
            if ($deviation > 0.005) {
                // User is slightly in profit, very gently slow down to 98%
                $localTargetRTP = 0.98;
            } elseif ($deviation < -0.005) {
                // Safety Net: Immediate boost to 150% RTP to force a recovery
                $localTargetRTP = 1.50 * $chaosFactor;
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
                // User is winning, suppress high multipliers aggressively.
                // Maximum allowed is 20x, but 5x-10x is most likely.
                if ($m > 20) {
                    $weights[] = 0;
                } elseif ($m > 5) {
                    $weights[] = (int)($baseWeight * 0.1); // 90% reduction
                } else {
                    $weights[] = $baseWeight * 3; // Focus on 5x
                }
            } elseif ($deviation < -0.15) {
                // User is in deficit, high multipliers are much more likely
                if ($m >= 250) $weights[] = $baseWeight * 5 * $chaos;
                else $weights[] = $baseWeight;
            } else {
                // Normal state
                $weights[] = $baseWeight * $chaos;
            }
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function getNaturalWeight(int $multiplier): int
    {
        return match ($multiplier) {
            5 => 500, 10 => 300, 20 => 100, 50 => 50, 
            100 => 20, 250 => 10, 500 => 5, 1000 => 2,
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
