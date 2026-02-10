<?php

namespace App\Services\FairLuck;

use App\Models\UserLuckProfile;

class DualAlgorithmEngine
{
    /**
     * Algorithm 1: Macro Controller (The Watcher)
     * Determines if the "High Multiplier" cycle is active.
     */
    public function isMacroAllowed(UserLuckProfile $profile): bool
    {
        // If we are in recovery mode, Macro is silenced
        if ($profile->is_in_recovery) {
            return false;
        }

        // Macro speaks when user is in deficit or balanced
        // If user is already رابح (surplus), we don't trigger Macro until they lose some
        return $profile->total_profit <= 0;
    }

    /**
     * Algorithm 2: Micro Controller (The Recoverer)
     * Handles the user after a big win to recover profits for the app.
     */
    public function getMicroAdjustment(UserLuckProfile $profile): array
    {
        if (!$profile->is_in_recovery) {
            return [
                'prob_multiplier' => 1.0,
                'max_multiplier' => 1000,
                'force_low_multipliers' => false
            ];
        }

        // Recovery Mode Logic:
        // We want to recover the surplus but keep the user engaged.
        // Randomized probability multiplier to avoid fixed rhythm (0.4 to 0.7)
        $probMultiplier = mt_rand(40, 70) / 100;

        return [
            'prob_multiplier' => $probMultiplier,
            'max_multiplier' => 50, // Allow up to 50x as "bait" wins
            'force_low_multipliers' => true
        ];
    }

    /**
     * Update the state of the dual algorithm after a bet result.
     */
    public function updateState(UserLuckProfile $profile, float $betAmount, float $winAmount, bool $isWinner): void
    {
       if ($isWinner && $winAmount >= ($betAmount * 50)) {
           // Big win detected! Trigger Recovery Mode
           $profile->is_in_recovery = true;
           // Target is to reach a slight deficit (e.g., -1% of total bets)
           $profile->recovery_target_profit = -($profile->total_bets * 0.01);
       }

       if ($profile->is_in_recovery) {
           // Check if we have recovered enough
           if ($profile->total_profit <= $profile->recovery_target_profit) {
               $profile->is_in_recovery = false;
           }
       }
    }
}
