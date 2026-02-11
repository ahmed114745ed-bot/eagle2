<?php

namespace App\Services\FairLuck;

use App\Models\User;
use App\Models\Gift;
use App\Models\FairLuckTransaction;
use App\Models\FairLuckSetting;
use Illuminate\Support\Facades\DB;

class FairLuckServiceDualManaged
{
    protected $profileManager;
    protected $deviationCalculator;
    protected $dualEngine;

    public function __construct(
        ProfileManager $profileManager,
        DeviationCalculator $deviationCalculator,
        DualAlgorithmEngine $dualEngine
    ) {
        $this->profileManager = $profileManager;
        $this->deviationCalculator = $deviationCalculator;
        $this->dualEngine = $dualEngine;
    }

    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId) {
            $profile = $this->profileManager->getProfile($user->id);
            $deviationBefore = (float) $profile->current_deviation;

            // 1. Playtime Maximizer Logic (Target 99% RTP / 1% App Profit)
            $targetRTP = 0.99; 
            
            // Calculate what probability we NEED to hit 99% based on current multiplier distribution
            // This ensures the app profit is exactly 1% on the long run.
            $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
            $weights = [800, 400, 150, 40, 10, 5, 2, 1];
            $expectedMultiplier = 0;
            $totalWeight = array_sum($weights);
            foreach ($multipliers as $i => $m) {
                $expectedMultiplier += $m * ($weights[$i] / $totalWeight);
            }
            
            $idealProb = $targetRTP / $expectedMultiplier;

            // Blend with Admin Probability (Admin sets the "feel", we ensure the "profit")
            $adminProb = (float) ($gift->luckyGift?->win_probability ?? ($idealProb * 100)) / 100;
            
            // If admin prob is too high/low, we slowly gravitate towards idealProb to keep 1% profit
            $probability = ($adminProb * 0.3) + ($idealProb * 0.7);

            // 2. Dual Engine Micro Adjustment
            $microAdjust = $this->dualEngine->getMicroAdjustment($profile);
            $probability = $probability * $microAdjust['prob_multiplier'];

            // 3. Determine Win/Loss
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $probability;

            $multiplier = 0;
            if ($isWinner) {
                // ALLOW 1000x freely by checking deviation only for weights, not hard block
                $multiplier = $this->selectManagedMultiplier($deviationBefore, $microAdjust['force_low_multipliers']);
            }

            $profitAmount = $isWinner ? (($multiplier * $betAmount) - $betAmount) : -$betAmount;

            // Update Stats
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount
            );

            $this->profileManager->updateStats($profile, $betAmount, $profitAmount, $isWinner, $newDeviation);
            $this->dualEngine->updateState($profile, $betAmount, $isWinner ? ($multiplier * $betAmount) : 0, $isWinner);
            $profile->save();

            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'is_winner' => $isWinner,
                'multiplier' => $isWinner ? $multiplier : null,
                'profit_amount' => $profitAmount,
                'deviation_before' => $deviationBefore,
                'calculated_probability' => $probability,
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

    private function selectManagedMultiplier(float $deviation, bool $forceLow): int
    {
        $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
        if ($forceLow) {
            $weights = [500, 300, 100, 30, 0, 0, 0, 0]; // No 1000x in Recovery
        } else {
            // Natural weights allowing 1000x
            $weights = [500, 300, 100, 80, 40, 20, 10, 5]; 
            if ($deviation < -0.1) { // Boost high multipliers if losing
                $weights[7] = 20; // 1000x weight increase
                $weights[6] = 30; // 500x weight increase
            }
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) return $values[0];
        $random = rand(1, $totalWeight);
        $currentWeight = 0;
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) return $value;
        }
        return $values[0];
    }
}
