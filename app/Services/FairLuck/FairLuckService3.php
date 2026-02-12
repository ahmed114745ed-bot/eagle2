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
        private MultiplierSelector $multiplierSelector,
        private HighMultiplierLedger $highMultiplierLedger
    ) {}

    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId) {
            // 1. Load context
            $profile = $this->profileManager->getProfile($user->id);
            $deviation = (float) $profile->current_deviation;
            
            // --- LOAD STATS FIRST ---
            $lossKey = "fairluck_loss_streak_" . $user->id;
            $jackpotKey = "fairluck_jackpot_pity_" . $user->id;
            $contributionKey = "fairluck_contribution_bank_" . $user->id;
            $drainLockKey = "fairluck_drain_lock_" . $user->id;

            $consecutiveLosses = (int) (\Illuminate\Support\Facades\Redis::get($lossKey) ?? 0);
            $jackpotPity = (int) (\Illuminate\Support\Facades\Redis::get($jackpotKey) ?? 0);
            $contributionBank = (int) (\Illuminate\Support\Facades\Redis::get($contributionKey) ?? 0);
            $isDrainLocked = (bool) \Illuminate\Support\Facades\Redis::get($drainLockKey);

            // Require the player to "pre-pay" a large chunk of the jackpot via distributed losses
            $requiredContribution = (int) max(1, round($betAmount * 150));
            $unlockContribution = (int) max(1, round($requiredContribution * 0.5));
            $hasPaidForJackpot = $unlockContribution > 0 && $contributionBank >= $unlockContribution;

            $eligibilitySignal = $this->highMultiplierLedger->evaluateEligibility($user->id, $betAmount);

            // 2. Calculate "System Mood" (Chaos Factor)
            $chaosFactor = mt_rand(85, 115) / 100;

            // 3. Dynamic RTP Adjustment
            // Default target for stability during the "Middle Distribution"
            $targetLossRate = 0.03; // Begin near-even to keep bankroll hovering ~30k

            if ($jackpotPity > 400) {
                $targetLossRate = max($targetLossRate, 0.08);
            }

            if ($jackpotPity > 600) {
                $targetLossRate = max($targetLossRate, 0.18);
            }

            if ($jackpotPity > 700) {
                $targetLossRate = max($targetLossRate, 0.28);
            }

            if ($jackpotPity > 800) {
                $targetLossRate = max($targetLossRate, 0.40);
            }

            if ($jackpotPity > 1000) {
                // Post-jackpot or persistent winner drain
                $targetLossRate = max($targetLossRate, 0.55); 
            }

            if ($isDrainLocked) {
                $targetLossRate = max($targetLossRate, 0.95);
            }
            
            $targetRTP = (1.0 - $targetLossRate);
            $localTargetRTP = $targetRTP; 
            
            // Adaptive scaling based on deviation (lifetime performance)
            if ($deviation >= 0.05) {
                // EXTREME recovery if player is in profit > 5%
                $localTargetRTP = 0.001 * $chaosFactor;
            } elseif ($deviation >= 0) {
                $localTargetRTP = 0.02 * $chaosFactor;
            } elseif ($deviation < -0.15) {
                // Generous if they are losing too fast
                $localTargetRTP = 0.98 * $chaosFactor;
            }

            if ($isDrainLocked) {
                $localTargetRTP = min($localTargetRTP, 0.01 * $chaosFactor);
            }

            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            $baseProb = ($localTargetRTP / $expectedMultiplier);

            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $finalProbability = $this->probabilityEngine->calculate($baseProb, $deviation, $protectionMultiplier);
            
            // Forced Win Loop: Avoid losing streaks longer than ~20 for small wins, capped at 50 overall.
            $maxLossStreak = min(35, max(15, (int) floor($jackpotPity / 60) + 15));
            if ($isDrainLocked) {
                $maxLossStreak = min($maxLossStreak, 22);
            }

            if ($consecutiveLosses >= 50) {
                $finalProbability = 1.0; 
            } elseif ($consecutiveLosses >= $maxLossStreak) {
                $finalProbability = max($finalProbability, $isDrainLocked ? 0.25 : 0.22); 
            } elseif ($consecutiveLosses >= 5) {
                $finalProbability = max($finalProbability, $isDrainLocked ? 0.12 : 0.11); 
            } elseif ($consecutiveLosses >= 3) {
                $finalProbability = max($finalProbability, $isDrainLocked ? 0.10 : 0.08);
            }

            if ($consecutiveLosses >= 4 && $consecutiveLosses < 50 && $consecutiveLosses % 4 === 0) {
                $finalProbability = max($finalProbability, $isDrainLocked ? 0.18 : 0.15);
            }

            if ($isDrainLocked) {
                $finalProbability = min($finalProbability, 0.12);
            }

            if ($eligibilitySignal->probabilityFloor > 0) {
                $finalProbability = max($finalProbability, $eligibilitySignal->probabilityFloor);
            }

            $forceMiniWins = false;
            if (!$isDrainLocked && $jackpotPity < 800 && $user->di <= ($betAmount * 20)) {
                $finalProbability = max($finalProbability, 0.32);
                $forceMiniWins = true;
            }
            
            // Targeted Jackpot Guarantee: Trigger every 800 - 1000 bets as requested
            $forceJackpot = false;
            if (!$isDrainLocked) {
                $nearBankrupt = $user->di <= ($betAmount * 20);
                if ($jackpotPity >= 800 && $jackpotPity <= 1000 && $deviation < 0.2 && $hasPaidForJackpot) {
                    $finalProbability = max($finalProbability, 0.10); 
                    $forceJackpot = true;
                }
                if (!$forceJackpot && $jackpotPity >= 780 && $jackpotPity < 800 && $hasPaidForJackpot && $nearBankrupt) {
                    $finalProbability = max($finalProbability, 0.14);
                    $forceJackpot = true;
                }
                if ($jackpotPity > 1100 && $hasPaidForJackpot) {
                    $forceJackpot = true;
                }
                if ($jackpotPity > 1250 && $hasPaidForJackpot) {
                    $forceJackpot = true;
                }
            }

            if (!$forceJackpot && $eligibilitySignal->forceJackpot) {
                $forceJackpot = true;
            }

            // Add extra randomness layer
            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;

            // 5. Intelligent Multiplier Selection (The Bait Logic)
            $multiplier = 0;
            if ($isWinner) {
                $isBeginner = $this->beginnerProtection->isUnderProtection($profile);
                // Instead of a simple choice, we shift weights dynamically based on user's current session "luck"
                $multiplier = $this->smartMultiplierSelect(
                    $deviation,
                    $chaosFactor,
                    $consecutiveLosses,
                    $isBeginner,
                    $forceJackpot,
                    $jackpotPity,
                    $contributionBank,
                    $unlockContribution,
                    $isDrainLocked,
                    $forceMiniWins,
                    $eligibilitySignal
                );
                
                \Illuminate\Support\Facades\Redis::del($lossKey);
                if ($multiplier >= 250) {
                    \Illuminate\Support\Facades\Redis::del($jackpotKey);
                    $postJackpotDebt = $unlockContribution > 0 ? $unlockContribution : $betAmount;
                    \Illuminate\Support\Facades\Redis::set($contributionKey, -$postJackpotDebt);
                    \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                    \Illuminate\Support\Facades\Redis::setex($drainLockKey, 86400, 1);
                } else {
                    \Illuminate\Support\Facades\Redis::incr($jackpotKey);
                    if ($unlockContribution > 0 && $contributionBank > -$unlockContribution) {
                        $profit = max(0, ($multiplier - 1) * $betAmount);
                        $recoveryRatio = $multiplier >= 50 ? 0.75 : 0.35;
                        $profitOffset = $profit * $recoveryRatio;
                        $updatedBank = max(-$unlockContribution, $contributionBank - $profitOffset);
                        \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                        \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                    }
                }
            } else {
                \Illuminate\Support\Facades\Redis::incr($lossKey);
                \Illuminate\Support\Facades\Redis::incr($jackpotKey);
                $updatedBank = min($contributionBank + $betAmount, $requiredContribution * 5);
                \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                \Illuminate\Support\Facades\Redis::expire($lossKey, 3600); 
                \Illuminate\Support\Facades\Redis::expire($jackpotKey, 86400); 
                \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200); 
            }

            // 6. Update Stats
            $profitAmount = $isWinner ? (($multiplier * $betAmount) - $betAmount) : -$betAmount;

            $this->highMultiplierLedger->recordOutcome(
                $user->id,
                $betAmount,
                $profitAmount,
                $isWinner,
                $multiplier
            );
            
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
    private function smartMultiplierSelect(
        float $deviation,
        float $chaos,
        int $streak = 0,
        bool $isBeginner = false,
        bool $forceJackpot = false,
        int $pityCount = 0,
        int $contributionBank = 0,
        int $unlockContribution = 0,
        bool $isDrainLocked = false,
        bool $forceMiniWins = false,
        ?HighMultiplierSignal $highMultiplierSignal = null
    ): int
    {
        $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
        $weights = [];
        $hasPaidForJackpot = $unlockContribution > 0 && $contributionBank >= $unlockContribution;
        $jackpotScaling = $unlockContribution > 0
            ? min(5.0, max(1.0, $contributionBank / $unlockContribution))
            : 1.0;

        foreach ($multipliers as $m) {
            $baseWeight = $this->getNaturalWeight($m);
            $weight = 0.0;

            // Logic 0: Survival Mini-Wins (pre-jackpot bailout)
            if ($forceMiniWins && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 40,
                    10 => $baseWeight * 6,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            // Logic 1: Drain Lock Mode (restrict wins to tiny bait payouts)
            if ($isDrainLocked && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 40,
                    10 => $baseWeight * 4,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            // Logic 2: Forced Jackpot Mode (Ensure they get 250x+ every 800-1000 bets)
            if ($forceJackpot) {
                $weight = match ($m) {
                    250 => $baseWeight * 250,
                    500 => $baseWeight * 10,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            if ($m >= 250 && !$hasPaidForJackpot) {
                $progress = $unlockContribution > 0 ? ($contributionBank / $unlockContribution) : 0;
                if ($progress < 0.75) {
                    // Do not allow large multipliers until the bankroll has "paid" for them
                    $weights[] = 0;
                    continue;
                }
            }

            // Logic 4: "Middle Distribution" (Phase 0-500 hits)
            // Focus on small profits and losses (5x, 10x, 20x) to keep user engaged without big swings.
            if ($pityCount < 500 && $deviation < 0.1 && $deviation > -0.1) {
                $weight = match (true) {
                    $m == 5 => $baseWeight * 30,
                    $m == 10 => $baseWeight * 10,
                    $m == 20 => $baseWeight * 2,
                    $m <= 100 => $baseWeight * 0.25,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            // Logic 3: Engagement & Drain Mode
            // Allow 250x+ even in profit, but with very controlled weights to prevent huge jumps.
            if ($streak >= 3 || $deviation >= -0.05) {
                if ($m >= 250) {
                    // EXTREME Drain: If balance/deviation is very positive, kill high multipliers.
                    if ($deviation > 0.15) {
                        $weight = 0;
                    } else {
                        // Very rare occurrence in normal transitions (scaled by how much they prepaid)
                        $weight = max(0.01, $baseWeight * 0.05 * $chaos * $jackpotScaling);
                    }
                } elseif ($m > 20) {
                    $weight = $baseWeight * 0.1;
                } elseif ($m == 5) {
                    $weight = $baseWeight * 10;
                } else {
                    $weight = $baseWeight * 5;
                }
            }
            // Logic 4: Small "Hope" Wins
            // If user is losing (Deviation -15% to -35%), allow 250x-500x more often
            elseif ($deviation < -0.15 && $deviation > -0.35) {
                if ($m >= 500) {
                    $weight = $baseWeight * 0.5 * $chaos;
                } elseif ($m >= 250) {
                    $weight = $baseWeight * 2 * $chaos;
                } else {
                    $weight = $baseWeight;
                }
            }
            // Logic 5: Recovery Tiers
            else {
                if ($m >= 250) {
                    $weight = $baseWeight * 3 * $chaos * $jackpotScaling;
                } else {
                    $weight = $baseWeight * $chaos;
                }
            }

            $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function finalizeMultiplierWeight(float $weight, int $multiplier, ?HighMultiplierSignal $highMultiplierSignal): int
    {
        if ($weight <= 0) {
            return 0;
        }

        if ($highMultiplierSignal && $highMultiplierSignal->hasPriority($multiplier)) {
            $weight *= max(1.0, $highMultiplierSignal->weightFor($multiplier));
        }

        return (int) max(0, round($weight));
    }

    private function getNaturalWeight(int $multiplier): int
    {
        return match ($multiplier) {
            5 => 800, 10 => 350, 20 => 120, 50 => 30, 
            100 => 12, 250 => 6, 500 => 3, 1000 => 1,
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
