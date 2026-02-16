<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckTransaction;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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
    private const GLOBAL_VAULT_KEY = 'fairluck_global_vault';
    private const GLOBAL_SAFETY_BUFFER = 5000;
    private float $houseEdgeRate;

    public function __construct(
        private ProfileManager $profileManager,
        private DeviationCalculator $deviationCalculator,
        private BeginnerProtection $beginnerProtection,
        private ProbabilityEngine $probabilityEngine,
        private MultiplierSelector $multiplierSelector,
        private HighMultiplierLedger $highMultiplierLedger,
        private LossLedger $lossLedger
    ) {
        $this->houseEdgeRate = (float) config('fairluck.house_edge_rate', 0.02);
    }

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

            $globalVault = $this->getGlobalVaultBalance();
            $betUnit = max(1, (int) round($betAmount));
            $globalVaultInt = (int) round($globalVault);
            $lossScoreBefore = $this->calculateLossScore($deviation, $contributionBank, $jackpotPity);
            $this->updateLossLeaderboard(
                $user->id,
                $lossScoreBefore,
                $contributionBank,
                $jackpotPity,
                $consecutiveLosses
            );
            $isTopLossCandidate = $this->isTopLossCandidate($user->id);

            // Require the player to "pre-pay" a large chunk of the jackpot via distributed losses
            // Make it dynamic based on user balance to allow smaller bankrolls to access high multipliers
            $userBalance = (int) $user->di;
            if ($userBalance <= 5000) {
                $multiplierFactor = 10;
            } elseif ($userBalance <= 20000) {
                $multiplierFactor = 50;
            } else {
                $multiplierFactor = 150;
            }

            $requiredContribution = (int) max($betUnit, round($betUnit * $multiplierFactor));
            $unlockContribution = (int) max(1, round($requiredContribution * 0.35));
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
                    $eligibilitySignal,
                    $globalVaultInt,
                    $isTopLossCandidate,
                    $betUnit
                );
                
                \Illuminate\Support\Facades\Redis::del($lossKey);
                if ($multiplier >= 250) {
                    $jackpotPayout = max(0, ($multiplier - 1) * $betAmount);
                    \Illuminate\Support\Facades\Redis::del($jackpotKey);
                    $postJackpotDebt = $unlockContribution > 0 ? $unlockContribution : $betAmount;
                    \Illuminate\Support\Facades\Redis::set($contributionKey, -$postJackpotDebt);
                    \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                    \Illuminate\Support\Facades\Redis::setex($drainLockKey, 86400, 1);
                    $this->lossLedger->markHighMultiplierAwarded($user->id, (int) round($jackpotPayout));
                } else {
                    \Illuminate\Support\Facades\Redis::incr($jackpotKey);
                    if ($unlockContribution > 0 && $contributionBank > -$unlockContribution) {
                        $profit = max(0, ($multiplier - 1) * $betAmount);
                        $recoveryRatio = $multiplier >= 50 ? 0.75 : 0.35;
                        $profitOffset = $profit * $recoveryRatio;
                        $updatedBank = max(-$unlockContribution, $contributionBank - $profitOffset);
                        \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                        \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                        if ($profitOffset > 0) {
                            $this->lossLedger->removeFromGlobalPool((int) round($profitOffset));
                        }
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
                $this->lossLedger->addToGlobalPool((int) round($betAmount));
            }

            $this->settleGlobalVaultBalance($isWinner, (int) $multiplier, $betAmount);

            // 6. Update Stats
            $profitAmount = $isWinner ? (($multiplier * $betAmount) - $betAmount) : -$betAmount;

            $houseEdgeCut = $this->calculateHouseEdgeCut($betAmount, $isWinner, (int) $multiplier);
            if ($houseEdgeCut > 0) {
                $this->increaseGlobalVaultBalance($houseEdgeCut);
            }

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

            $updatedContributionBank = (int) (Redis::get($contributionKey) ?? 0);
            $updatedJackpotPity = (int) (Redis::get($jackpotKey) ?? 0);
            $updatedLossMomentum = (int) (Redis::get($lossKey) ?? 0);
            $this->updateLossLeaderboard(
                $user->id,
                $this->calculateLossScore($newDeviation, $updatedContributionBank, $updatedJackpotPity),
                $updatedContributionBank,
                $updatedJackpotPity,
                $updatedLossMomentum
            );

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
                'mood' => $localTargetRTP > 1 ? 'Generous' : ($localTargetRTP < 0.95 ? 'Recovery' : 'Stable'),
                'houseCut' => $houseEdgeCut,
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
        ?HighMultiplierSignal $highMultiplierSignal = null,
        int $globalVaultBalance = 0,
        bool $isTopLossCandidate = false,
        int $betUnit = 0
    ): int {
        $multipliers = [5, 10, 20, 50, 100, 250, 500, 1000];
        $weights = [];
        $hasPaidForJackpot = $unlockContribution > 0 && $contributionBank >= $unlockContribution;
        $jackpotScaling = $unlockContribution > 0
            ? min(5.0, max(1.0, $contributionBank / $unlockContribution))
            : 1.0;

        $cautiousDistribution = $this->shouldUseCautiousDistribution(
            $deviation,
            $contributionBank,
            $unlockContribution,
            $isDrainLocked
        );

        $ratingApplies = static fn (int $multiplier): bool => $multiplier >= 100;

        foreach ($multipliers as $m) {
            $baseWeight = $this->getNaturalWeight($m);
            $weight = 0.0;

            if ($forceMiniWins && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 40,
                    10 => $baseWeight * 6,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            if ($isDrainLocked && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 35,
                    10 => $baseWeight * 4,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            if ($forceJackpot) {
                if ($m >= 250 &&
                    !$this->canDisburseHighMultiplier(
                        $m,
                        $betUnit,
                        $globalVaultBalance,
                        $isTopLossCandidate,
                        $hasPaidForJackpot,
                        $deviation,
                        $pityCount
                    )) {
                    $weights[] = 0;
                    continue;
                }

                $weight = match ($m) {
                    250 => $baseWeight * 250,
                    500 => $baseWeight * 10,
                    default => 0,
                };
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            if (!$ratingApplies($m)) {
                $weight = $cautiousDistribution
                    ? $this->cautiousDistributionWeight($m, $baseWeight, $streak)
                    : $this->neutralSmallWinWeight($m, $baseWeight, $chaos, $streak, $isBeginner);
                $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
                continue;
            }

            if ($m >= 250 && !$hasPaidForJackpot) {
                $progress = $unlockContribution > 0 ? ($contributionBank / $unlockContribution) : 0;
                if ($progress < 0.75) {
                    $weights[] = 0;
                    continue;
                }
            }
            if ($ratingApplies($m) &&
                !$this->canDisburseHighMultiplier(
                    $m,
                    $betUnit,
                    $globalVaultBalance,
                    $isTopLossCandidate,
                    $hasPaidForJackpot,
                    $deviation,
                    $pityCount
                )) {
                $weights[] = 0;
                continue;
            }

            $weight = $this->ratingSensitiveWeight(
                $m,
                $baseWeight,
                $deviation,
                $chaos,
                $streak,
                $jackpotScaling,
                $pityCount,
                $isDrainLocked,
                $isBeginner
            );

            $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
        }

        return $this->weightedRandom($multipliers, $weights);
    }

    private function shouldUseCautiousDistribution(
        float $deviation,
        int $contributionBank,
        int $unlockContribution,
        bool $isDrainLocked
    ): bool {
        if ($isDrainLocked) {
            return true;
        }

        if ($deviation >= -0.02) {
            return true;
        }

        if ($unlockContribution > 0 && $contributionBank < $unlockContribution) {
            return true;
        }

        return false;
    }

    private function cautiousDistributionWeight(int $multiplier, float $baseWeight, int $streak): float
    {
        $streakPenalty = max(0.4, 1 - min(0.6, $streak * 0.04));

        return match ($multiplier) {
            5 => $baseWeight * 18 * $streakPenalty,
            10 => $baseWeight * 5 * $streakPenalty,
            20 => $baseWeight * 1.5 * $streakPenalty,
            50 => $baseWeight * 0.35,
            default => 0.0,
        };
    }

    private function neutralSmallWinWeight(
        int $multiplier,
        float $baseWeight,
        float $chaos,
        int $streak,
        bool $isBeginner
    ): float {
        $streakBoost = 1 + min(0.5, $streak * 0.05);
        $beginnerBoost = $isBeginner ? 1.3 : 1.0;

        return match ($multiplier) {
            5 => $baseWeight * 10 * $chaos * $streakBoost * $beginnerBoost,
            10 => $baseWeight * 4 * $chaos * $beginnerBoost,
            20 => $baseWeight * 1.5 * $chaos,
            50 => $baseWeight * 0.7 * $chaos,
            default => 0.0,
        };
    }

    private function ratingSensitiveWeight(
        int $multiplier,
        float $baseWeight,
        float $deviation,
        float $chaos,
        int $streak,
        float $jackpotScaling,
        int $pityCount,
        bool $isDrainLocked,
        bool $isBeginner
    ): float {
        if ($isDrainLocked && $multiplier >= 250) {
            return 0.0;
        }

        $weight = $baseWeight * 0.25 * $chaos * $jackpotScaling;

        if ($deviation >= 0.2) {
            $weight = $baseWeight * 0.01;
        } elseif ($deviation >= 0.05) {
            $weight = $baseWeight * 0.05 * $chaos;
        } elseif ($deviation <= -0.3) {
            $weight = $baseWeight * 3.5 * $chaos * $jackpotScaling;
        } elseif ($deviation <= -0.1) {
            $weight = $baseWeight * 1.8 * $chaos * $jackpotScaling;
        }

        if ($pityCount > 600) {
            $weight *= 1 + min(1.5, ($pityCount - 600) / 400);
        }

        if ($streak >= 8) {
            $weight *= 1.25;
        }

        if ($isBeginner && $multiplier === 100) {
            $weight *= 1.5;
        }

        return $weight;
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

    private function getGlobalVaultBalance(): int
    {
        return (int) (Redis::get(self::GLOBAL_VAULT_KEY) ?? 0);
    }

    private function increaseGlobalVaultBalance(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        Redis::incrby(self::GLOBAL_VAULT_KEY, $amount);
        Redis::expire(self::GLOBAL_VAULT_KEY, 2592000);
    }

    private function decreaseGlobalVaultBalance(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $current = $this->getGlobalVaultBalance();
        $next = max(0, $current - $amount);
        Redis::set(self::GLOBAL_VAULT_KEY, $next);
        Redis::expire(self::GLOBAL_VAULT_KEY, 2592000);
    }

    private function settleGlobalVaultBalance(bool $isWinner, int $multiplier, float $betAmount): void
    {
        if ($isWinner) {
            $profitPortion = max(0, ($multiplier - 1) * $betAmount);
            $this->decreaseGlobalVaultBalance((int) round($profitPortion));
            return;
        }

        $this->increaseGlobalVaultBalance((int) round($betAmount));
    }

    private function calculateLossScore(float $deviation, int $contributionBank, int $jackpotPity): float
    {
        $lossPressure = max(0, -$deviation) * 1000;
        $bankPressure = max(0, $contributionBank) / 50;
        $pityPressure = max(0, $jackpotPity) / 5;

        $score = -1 * ($lossPressure + $bankPressure + $pityPressure);

        return $score;
    }

    private function updateLossLeaderboard(
        int $userId,
        float $lossScore,
        int $contributionBank,
        int $jackpotPity,
        int $lossMomentum
    ): void {
        $this->lossLedger->recordSnapshot(
            $userId,
            $lossScore,
            $contributionBank,
            $jackpotPity,
            $lossMomentum
        );
    }

    private function isTopLossCandidate(int $userId): bool
    {
        return $this->lossLedger->isPriorityHolder($userId);
    }

    private function canDisburseHighMultiplier(
        int $multiplier,
        int $betUnit,
        int $globalVaultBalance,
        bool $isTopLossCandidate,
        bool $hasPaidForJackpot,
        float $deviation,
        int $pityCount = 0
    ): bool {
        if ($multiplier < 250 || $betUnit <= 0) {
            return true;
        }

        if (!$hasPaidForJackpot) {
            return false;
        }

        $priorityUnlocked = $isTopLossCandidate
            || $pityCount >= 900
            || $deviation <= -0.35;

        if (!$priorityUnlocked) {
            return false;
        }

        $payoutPortion = max(0, ($multiplier - 1) * $betUnit);
        $required = $payoutPortion + self::GLOBAL_SAFETY_BUFFER;

        return $this->lossLedger->poolCanCover($required)
            && $globalVaultBalance >= $required;
    }

    private function calculateHouseEdgeCut(float $betAmount, bool $isWinner, int $multiplier): int
    {
        if ($this->houseEdgeRate <= 0) {
            return 0;
        }

        $base = $betAmount;
        if ($isWinner && $multiplier > 0) {
            $base = max($base, $multiplier * $betAmount);
        }

        $cut = (int) floor($base * $this->houseEdgeRate);

        if ($cut <= 0 && $base > 0) {
            $cut = 1;
        }

        return (int) min($cut, (int) ceil($base));
    }
}
