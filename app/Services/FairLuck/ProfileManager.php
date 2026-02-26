<?php

namespace App\Services\FairLuck;

use App\Models\UserLuckProfile;


class ProfileManager
{
    /**
     * Get or create user luck profile.
     */
    public function getProfile(int $userId): UserLuckProfile
    {
        return UserLuckProfile::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_bets' => 0,
                'total_profit' => 0,
                'bet_count' => 0,
                'win_count' => 0,
                'current_deviation' => 0,
                'is_legacy_user' => false // Will be updated by migration scripts if needed
            ]
        );
    }

    /**
     * Update profile stats after a bet.
     */
    public function updateStats(UserLuckProfile $profile, float $betAmount, float $profitAmount, bool $isWinner, float $newDeviation)
    {
        $profile->total_bets += $betAmount;
        $profile->total_profit += $profitAmount;
        $profile->bet_count += 1;
        
        if ($isWinner) {
            $profile->win_count += 1;
        }

        if (!$profile->first_bet_at) {
            $profile->first_bet_at = now();
        }

        $profile->current_deviation = $newDeviation;
        $profile->save();
    }
}
