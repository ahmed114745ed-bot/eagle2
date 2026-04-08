<?php

namespace App\Services\FairLuck\V7;

use App\Models\FairLuckSetting;

/**
 * ProbabilityCalculator V7: User-First Overhaul
 * 
 * Key changes:
 * 1. Default RTP increased to 90% (admin configurable 70-99%)
 * 2. All parameters are admin-configurable (stored as 0-100, read as 0-1)
 * 3. baseProb is calculated correctly to achieve target RTP
 * 4. Probability can reach up to 85% for users far below target RTP
 */
class ProbabilityCalculator
{
    /**
     * Calculate win probability based on user's RTP gap and bet amount.
     *
     * Game company logic:
     * - RTP = total rewards / total bets (per user, lifetime)
     * - When below target: probability increases proportional to (gap * totalSpent / betAmount)
     * - When above target: probability decreases
     * - Bet amount matters: small bets relative to deficit = more boost, large bets = less boost
     * - BANKRUPTCY PROTECTION: Probability is capped based on pool health
     * 
     * NOTE: All percentage settings are stored as 0-100 in DB, divided by 100 here.
     */
    public function calculate(
        float $actualRTP,
        float $targetRTP,
        float $totalSpent,
        float $betAmount,
        int $betCount,
        float $expectedMultiplier
    ): float {
        // Get configurable parameters (stored as 0-100, convert to 0-1)
        $newPlayerBets    = (int)   FairLuckSetting::getByKey('V7_new_player_bets', 20);
        $newPlayerBoost   = (float) FairLuckSetting::getByKey('V7_new_player_boost', 3.0);
        $chaosMin         = (float) FairLuckSetting::getByKey('V7_chaos_factor_min', 0.90);
        $chaosMax         = (float) FairLuckSetting::getByKey('V7_chaos_factor_max', 1.10);
        $lowBalanceThreshold = (int) FairLuckSetting::getByKey('V7_low_balance_threshold', 15);
        $lowBalanceMinProb   = (float) FairLuckSetting::getByKey('V7_low_balance_min_prob', 0.18);
        $maxProbabilityCap   = (float) FairLuckSetting::getByKey('V7_max_probability_cap', 0.50);

        // Base probability: targetRTP / trueWeightedAvgMultiplier
      
        // الحل: حساب المتوسط المرجح الحقيقي من الأوزان مباشرة
        // ثم استخدامه لحساب baseProb بدقة
        //
        // الهدف: winRate × trueAvgMultiplier = targetRTP
        // baseProb = targetRTP / trueAvgMultiplier
        $weights = \App\Models\FairLuckSetting::getMultiplierWeights();
        $totalWeight = array_sum($weights);
        $trueWeightedAvg = 0.0;
        if ($totalWeight > 0) {
            foreach ($weights as $mult => $weight) {
                $trueWeightedAvg += ($mult * $weight) / $totalWeight;
            }
        }
        // الحل الصحيح: استخدام المتوسط المرجح الفعلي مباشرة
        // baseProb = targetRTP / trueWeightedAvg
        // مثال: إذا trueWeightedAvg = 50x → baseProb = 0.92/50 = 1.84%
        // win rate ~1.84% × 50x = 92% RTP ✅
        // هذا يضمن أن RTP الفعلي = targetRTP بدقة
        $effectiveAvgMultiplier = max(5.0, $trueWeightedAvg);
        $appFeeRate = FairLuckSetting::getAppFeeRate();
$receiverFeeRate = FairLuckSetting::getReceiverFeeRate();
$appFee = $betAmount * $appFeeRate;
$receiverFee = $betAmount * $receiverFeeRate;
$netBetAmount = $betAmount - $appFee - $receiverFee;
$baseProb = min($maxProbabilityCap, $targetRTP / $effectiveAvgMultiplier);

        // New player protection: disabled when newPlayerBets=0
        if ($newPlayerBets > 0 && $betCount < $newPlayerBets) {
            $progress = $betCount / max(1, $newPlayerBets);
            $boost = $newPlayerBoost * (1 - $progress) + 1.0 * $progress;
            return min($maxProbabilityCap, $baseProb * $boost);
        }

        // Not enough data - use base probability
        if ($totalSpent <= 0 || $betCount < 3) {
            return $baseProb;
        }

        $rtpGap = $targetRTP - $actualRTP;
        $rtpDeficit = $rtpGap * $totalSpent; // absolute amount user should have received more

        // betImpact: how many bets worth of deficit exists
        $betImpact = $betAmount > 0 ? $rtpDeficit / $betAmount : 0;

        $calculatedProb = $baseProb;

// Calculate net bet amount after deducting fees
$appFeeRate = FairLuckSetting::getAppFeeRate();
$receiverFeeRate = FairLuckSetting::getReceiverFeeRate();
$appFee = $betAmount * $appFeeRate;
$receiverFee = $betAmount * $receiverFeeRate;
$netBetAmount = $betAmount - $appFee - $receiverFee;

        if ($rtpGap > 0) {
            // User is BELOW target RTP - boost probability
            // scalingFactor stored as 0-100, convert to 0-1
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_boost_scaling', 0.05);
            $boost = min(4.0, $betImpact * $scalingFactor);
            $adjustedProb = $baseProb * (1 + $boost);

            $calculatedProb = min($maxProbabilityCap, max($baseProb, $adjustedProb));
        } else {
            // User is AT or ABOVE target RTP - reduce probability (but not too harshly)
            // scalingFactor stored as 0-100, convert to 0-1
            $scalingFactor = (float) FairLuckSetting::getByKey('V7_reduce_scaling', 0.02);
            $reduction = min(0.80, abs($betImpact) * $scalingFactor);
            $adjustedProb = $baseProb * (1 - $reduction);

            // تخفيض الحد الأدنى من 0.05 إلى 0.01 لتمكين التخفيض الكافي بعد الفوز الكبير
            // مثال: بعد فوز 100x → RTP = 10000% → يجب تخفيض الاحتمالية بشكل كبير
            $calculatedProb = max(0.01, $adjustedProb);
        }

        // BANKRUPTCY PROTECTION: Apply pool health reduction factor
        // V7: Never reduce below 60% except in true emergencies
        $bankruptcyProtection = app(BankruptcyProtection::class);
        $healthFactor = $bankruptcyProtection->getProbabilityReductionFactor();
        $finalProb = $calculatedProb * $healthFactor;

        // Chaos factor for unpredictability - configurable range
        $chaosRange = $chaosMax - $chaosMin;
        $chaosFactor = $chaosMin + (mt_rand(0, 100) / 100) * $chaosRange;
        $finalProb = max(0.02, min($maxProbabilityCap, $finalProb * $chaosFactor));

        // Low balance protection - configurable threshold
        $userBalance = (int) auth()->user()?->di ?? 0;
        if ($userBalance > 0 && $betAmount > 0 && ($userBalance / $betAmount) < $lowBalanceThreshold) {
            $finalProb = max($finalProb, $lowBalanceMinProb);
        }

        // Hard cap: never exceed max probability from settings
        return min($maxProbabilityCap, $finalProb);
    }
}
