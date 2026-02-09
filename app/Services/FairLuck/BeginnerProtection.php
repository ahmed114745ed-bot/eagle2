<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;
use App\Models\UserLuckProfile;
use Carbon\Carbon;

class BeginnerProtection
{
    /**
     * Determine protection multiplier for the user.
     */
    public function getMultiplier(UserLuckProfile $profile): float
    {
        if ($profile->is_legacy_user) {
            return 1.0;
        }

        if (!$profile->first_bet_at) {
            return (float) FairLuckSetting::getByKey('beginner_boost_multiplier', 2.0);
        }

        $daysLimit = (int) FairLuckSetting::getByKey('beginner_protection_days', 7);
        $betsLimit = (int) FairLuckSetting::getByKey('beginner_protection_bets', 50);
        $maxProfitRate = (float) FairLuckSetting::getByKey('beginner_max_profit_rate', 0.20);
        $boostMultiplier = (float) FairLuckSetting::getByKey('beginner_boost_multiplier', 2.0);

        // Check if user exceeded protection limits
        $daysSinceFirst = $profile->first_bet_at->diffInDays(now());
        $betsCount = $profile->bet_count;
        $profitRate = $profile->total_bets > 0 ? $profile->total_profit / $profile->total_bets : 0;

        // Fully protected
        if ($daysSinceFirst < $daysLimit && $betsCount < $betsLimit && $profitRate < $maxProfitRate) {
            return $boostMultiplier;
        }

        // Gradual transition for next 10 bets after protection ends
        if ($betsCount >= $betsLimit && $betsCount < ($betsLimit + 10)) {
            $transitionProgress = ($betsCount - $betsLimit) / 10;
            return $boostMultiplier - ($transitionProgress * ($boostMultiplier - 1.0));
        }

        return 1.0;
    }

    public function isUnderProtection(UserLuckProfile $profile): bool
    {
        return $this->getMultiplier($profile) > 1.0;
    }
}
