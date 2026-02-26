<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class FairLuckService3
{
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

    public function processBet(User $user, Gift $gift, float $betAmount, ?int $roomId = null, $receiverId = null, float $appFee = 0, float $receiverFee = 0): object
    {
        return DB::transaction(function () use ($user, $gift, $betAmount, $roomId, $appFee, $receiverFee) {
            $totalAmount = $betAmount + $appFee + $receiverFee;

            $profile = $this->profileManager->getProfile($user->id);
            $deviation = (float) $profile->current_deviation;

            $lossKey = "fairluck_loss_streak_" . $user->id;
            $jackpotKey = "fairluck_jackpot_pity_" . $user->id;
            $contributionKey = "fairluck_contribution_bank_" . $user->id;
            $drainLockKey = "fairluck_drain_lock_" . $user->id;

            $consecutiveLosses = (int) (\Illuminate\Support\Facades\Redis::get($lossKey) ?? 0);
            $jackpotPity = (int) (\Illuminate\Support\Facades\Redis::get($jackpotKey) ?? 0);
            $contributionBank = (int) (\Illuminate\Support\Facades\Redis::get($contributionKey) ?? 0);
            $isDrainLocked = (bool) \Illuminate\Support\Facades\Redis::get($drainLockKey);

            $forceWin = $consecutiveLosses >= 15;

            $globalVault = $this->getGlobalVaultBalance();
            $poolBalance = $this->lossLedger->poolBalance();
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

            $this->distributeBetAmount($totalAmount);

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

            $chaosFactor = mt_rand(85, 115) / 100;

            $targetLossRate = 0.20;

            if ($jackpotPity > 400) {
                $targetLossRate = max($targetLossRate, 0.18);
            }

            if ($jackpotPity > 600) {
                $targetLossRate = max($targetLossRate, 0.22);
            }

            if ($jackpotPity > 700) {
                $targetLossRate = max($targetLossRate, 0.25);
            }

            if ($jackpotPity > 800) {
                $targetLossRate = max($targetLossRate, 0.30);
            }

            if ($jackpotPity > 1000) {

                $targetLossRate = max($targetLossRate, 0.45);
            }

            if ($isDrainLocked) {
                $targetLossRate = max($targetLossRate, 0.70);
            }

            $targetRTP = (1.0 - $targetLossRate);
            $localTargetRTP = $targetRTP;

            if ($deviation >= 0.05) {

                $localTargetRTP = 0.10 * $chaosFactor;
            } elseif ($deviation >= 0) {
                $localTargetRTP = 0.40 * $chaosFactor;
            } elseif ($deviation < -0.15) {
                $localTargetRTP = 0.92 * $chaosFactor;
            }

            if ($isDrainLocked) {
                $localTargetRTP = min($localTargetRTP, 0.15 * $chaosFactor);
            }

            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            $baseProb = ($localTargetRTP / $expectedMultiplier);

            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $finalProbability = $this->probabilityEngine->calculate($baseProb, $deviation, $protectionMultiplier);

            if ($consecutiveLosses >= 20) {
                $finalProbability = 1.0;
            } elseif ($consecutiveLosses >= 15) {
                $finalProbability = max($finalProbability, 0.60);
            } elseif ($consecutiveLosses >= 12) {
                $finalProbability = max($finalProbability, 0.35);
            }

            if ($isDrainLocked) {
                $finalProbability = min($finalProbability, 0.12);
            }

            if ($eligibilitySignal->probabilityFloor > 0) {
                $finalProbability = max($finalProbability, $eligibilitySignal->probabilityFloor);
            }

            $forceMiniWins = false;
            if (!$isDrainLocked && $jackpotPity < 800 && $user->di <= ($betAmount * 20)) {
                $finalProbability = max($finalProbability, 0.18);
                $forceMiniWins = true;
            }

            $forceJackpot = false;
            if (!$isDrainLocked) {
                $nearBankrupt = $user->di <= ($betAmount * 20);

                if ($jackpotPity >= 800 && $jackpotPity <= 1000 && $deviation < 0.2 && $hasPaidForJackpot) {
                    $baseJackpotProb = 0.10;
                    $adjustedJackpotProb = DeviationCalculator::calculateJackpotProbability(
                        $baseJackpotProb,
                        $betAmount,
                        $this->getJackpotWalletBalance(),
                        $this->getGlobalVaultBalance(),
                        250
                    );

                    $finalProbability = max($finalProbability, $adjustedJackpotProb);
                    if ($adjustedJackpotProb >= 0.05) {
                        $forceJackpot = true;
                    }
                }

                if (!$forceJackpot && $jackpotPity >= 780 && $jackpotPity < 800 && $hasPaidForJackpot && $nearBankrupt) {
                    $baseJackpotProb = 0.14;
                    $adjustedJackpotProb = DeviationCalculator::calculateJackpotProbability(
                        $baseJackpotProb,
                        $betAmount,
                        $this->getJackpotWalletBalance(),
                        $this->getGlobalVaultBalance(),
                        250
                    );

                    $finalProbability = max($finalProbability, $adjustedJackpotProb);
                    if ($adjustedJackpotProb >= 0.08) {
                        $forceJackpot = true;
                    }
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

            $random = mt_rand(0, 10000) / 10000;
            $isWinner = $random <= $finalProbability;

            $multiplier = 0;
            if ($isWinner || $forceWin) {
                $isBeginner = $this->beginnerProtection->isUnderProtection($profile);

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
                    $betUnit,
                    $poolBalance,
                    $hasPaidForJackpot,
                    $totalAmount
                );

                if ($multiplier === 0) {
                    $isWinner = false;
                    $forceWin = false;
                }

                \Illuminate\Support\Facades\Redis::del($lossKey);
                if ($multiplier >= 250) {
                    $jackpotPayout = max(0, $multiplier * $totalAmount);
                    \Illuminate\Support\Facades\Redis::del($jackpotKey);
                    $postJackpotDebt = $unlockContribution > 0 ? $unlockContribution : $betAmount;
                    \Illuminate\Support\Facades\Redis::set($contributionKey, -$postJackpotDebt);
                    \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                    \Illuminate\Support\Facades\Redis::setex($drainLockKey, 86400, 1);

                    $jackpotWalletBalance = $this->getJackpotWalletBalance();
                    if ($jackpotWalletBalance >= $jackpotPayout) {
                        $this->decreaseJackpotWalletBalance((int) round($jackpotPayout), "Win payout (Jackpot {$multiplier}x)", $user->id);
                    } else {
                        $isWinner = false;
                        $forceWin = false;
                        $multiplier = 0;
                    }

                    $this->lossLedger->markHighMultiplierAwarded($user->id, (int) round($jackpotPayout));
                } else {
                    \Illuminate\Support\Facades\Redis::incr($jackpotKey);

                    $profit = max(0, $multiplier * $totalAmount);

                    if (in_array($multiplier, [50, 70, 100])) {
                        $mediumWalletBalance = $this->getMediumWalletBalance();
                        if ($mediumWalletBalance >= $profit) {
                            $this->decreaseMediumWallet((int) round($profit), "Win payout (Medium {$multiplier}x)", $user->id);

                        } else {

                            $isWinner = false;
                            $forceWin = false;
                            $multiplier = 0;

                        }
                    } else {

                        if (in_array($multiplier, [5, 10, 20])) {
                            $globalVaultBalance = $this->getGlobalVaultBalance();
                            if ($globalVaultBalance >= $profit) {
                                $this->decreaseGlobalVaultBalance((int) round($profit), "Win payout (Global Vault {$multiplier}x)", $user->id);

                            } else {

                                if ($globalVaultBalance > 0) {
                                    $this->decreaseGlobalVaultBalance($globalVaultBalance, "Win payout (Global Vault {$multiplier}x)", $user->id);
                                }

                                $remainingProfit = $profit - $globalVaultBalance;
                                if ($unlockContribution > 0 && $contributionBank > -$unlockContribution && $remainingProfit > 0) {
                                    $recoveryRatio = 0.35;
                                    $profitOffset = min($remainingProfit, $contributionBank + $unlockContribution) * $recoveryRatio;
                                    $updatedBank = max(-$unlockContribution, $contributionBank - $profitOffset);
                                    \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                                    \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                                    if ($profitOffset > 0) {
                                        $this->lossLedger->removeFromGlobalPool((int) round($profitOffset));
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                \Illuminate\Support\Facades\Redis::incr($lossKey);
                \Illuminate\Support\Facades\Redis::incr($jackpotKey);
                $updatedBank = min($contributionBank + $totalAmount, $requiredContribution * 5);
                \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                \Illuminate\Support\Facades\Redis::expire($lossKey, 3600);
                \Illuminate\Support\Facades\Redis::expire($jackpotKey, 86400);
                \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);

                $this->lossLedger->addToGlobalPool((int) round($totalAmount));
                $this->increaseGlobalVaultBalance((int) round($totalAmount), "Loss bet (User loss)", $user->id);
            }

            $profitAmount = $isWinner || $forceWin ? ($multiplier * $totalAmount) : -$totalAmount;

            if ($isWinner || $forceWin) {

                \Illuminate\Support\Facades\Redis::del("fairluck:consecutive_losses:{$user->id}");
            } else {

                \Illuminate\Support\Facades\Redis::incr("fairluck:consecutive_losses:{$user->id}");
                \Illuminate\Support\Facades\Redis::expire("fairluck:consecutive_losses:{$user->id}", 86400);
            }

            $mediumWalletWin = 0;
            $jackpotWalletWin = 0;

            if ($isWinner && $multiplier > 1) {
                $winAmount = $multiplier * $betAmount;

                if ($multiplier >= 250) {
                    $jackpotWalletWin = $winAmount;
                } elseif ($multiplier >= 50 && $multiplier <= 100) {
                    $mediumWalletWin = $winAmount;
                }
            }

            $walletsBeforeDistribution = [
                'global_vault' => $this->getGlobalVaultBalance(),
                'jackpot_wallet' => $this->getJackpotWalletBalance(),
                'medium_wallet' => $this->getMediumWalletBalance(),
            ];

            $walletsAfterDistribution = [
                'global_vault' => $this->getGlobalVaultBalance(),
                'jackpot_wallet' => $this->getJackpotWalletBalance(),
                'medium_wallet' => $this->getMediumWalletBalance(),
            ];

            \Illuminate\Support\Facades\Log::info('FairLuckService3 AFTER DISTRIBUTION', [
                'user_id' => $user->id,
                'bet_amount' => $betAmount,
                'global_vault_after' => $walletsAfterDistribution['global_vault'],
                'jackpot_wallet_after' => $walletsAfterDistribution['jackpot_wallet'],
                'medium_wallet_after' => $walletsAfterDistribution['medium_wallet'],
                'global_vault_increase' => $walletsAfterDistribution['global_vault'] - $walletsBeforeDistribution['global_vault'],
                'jackpot_wallet_increase' => $walletsAfterDistribution['jackpot_wallet'] - $walletsBeforeDistribution['jackpot_wallet'],
                'medium_wallet_increase' => $walletsAfterDistribution['medium_wallet'] - $walletsBeforeDistribution['medium_wallet'],
                'expected_55_percent' => round($totalAmount * 0.55),
                'expected_15_percent' => round($totalAmount * 0.15),
                'expected_10_percent' => round($totalAmount * 0.10),
            ]);

            $houseEdgeCut = $totalAmount * 0.20; // 20% total fee/house cut

            $this->highMultiplierLedger->recordOutcome(
                $user->id,
                $totalAmount,
                $profitAmount,
                $isWinner,
                $multiplier
            );

            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $totalAmount,
                $profile->total_profit + $profitAmount
            );

            $this->profileManager->updateStats(
                $profile,
                $totalAmount,
                $profitAmount,
                $isWinner,
                $newDeviation
            );

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

            FairLuckTransaction::create([
                'user_id' => $user->id,
                'gift_id' => $gift->id,
                'bet_amount' => $betAmount,
                'app_fee' => $appFee,
                'receiver_fee' => $receiverFee,
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
                'mood' => $localTargetRTP > 0.85 ? 'Generous' : ($localTargetRTP < 0.75 ? 'Recovery' : 'Stable'),
                'houseCut' => $houseEdgeCut,
            ];
        });
    }

    private function validateMultiplierAvailability(int $selectedMultiplier, float $betAmount): int
    {
        $requiredPayout = max(0, ($selectedMultiplier - 1) * $betAmount);

        if (in_array($selectedMultiplier, [5, 10, 20])) {
            $globalBalance = $this->getGlobalVaultBalance();
            $limit = FairLuckWallet::getNegativeLimit();

            if ($globalBalance + $limit >= $requiredPayout) {
                return $selectedMultiplier;
            } else {
                if ($selectedMultiplier == 20) {
                    $requiredFor10 = max(0, (10 - 1) * $betAmount);
                    if ($globalBalance + $limit >= $requiredFor10) {

                        return 10;
                    }
                    $requiredFor5 = max(0, (5 - 1) * $betAmount);
                    if ($globalBalance + $limit >= $requiredFor5) {

                        return 5;
                    }
                } elseif ($selectedMultiplier == 10) {
                    $requiredFor5 = max(0, (5 - 1) * $betAmount);
                    if ($globalBalance + $limit >= $requiredFor5) {

                        return 5;
                    }
                }

                $mediumBalance = $this->getMediumWalletBalance();
                $requiredFor50 = max(0, (50 - 1) * $betAmount);
                if ($mediumBalance >= $requiredFor50) {

                    return 50;
                }

                return 0;
            }
        }

        if (in_array($selectedMultiplier, [50, 70, 100])) {
            $mediumBalance = $this->getMediumWalletBalance();

            if ($mediumBalance >= $requiredPayout) {
                return $selectedMultiplier;
            } else {
                if ($selectedMultiplier == 100) {
                    $requiredFor70 = max(0, (70 - 1) * $betAmount);
                    if ($mediumBalance >= $requiredFor70) {

                        return 70;
                    }
                    $requiredFor50 = max(0, (50 - 1) * $betAmount);
                    if ($mediumBalance >= $requiredFor50) {

                        return 50;
                    }
                }
                if ($selectedMultiplier == 70) {
                    $requiredFor50 = max(0, (50 - 1) * $betAmount);
                    if ($mediumBalance >= $requiredFor50) {

                        return 50;
                    }
                }

                $globalBalance = $this->getGlobalVaultBalance();
                $requiredFor5 = max(0, (5 - 1) * $betAmount);
                if ($globalBalance >= $requiredFor5) {

                    return 5;
                }

                return 0;
            }
        }

        if (in_array($selectedMultiplier, [250, 500, 1000])) {
            $jackpotBalance = $this->getJackpotWalletBalance();

            if ($jackpotBalance >= $requiredPayout) {
                return $selectedMultiplier;
            } else {
                if ($selectedMultiplier == 500) {
                    $requiredFor250 = max(0, (250 - 1) * $betAmount);
                    if ($jackpotBalance >= $requiredFor250) {

                        return 250;
                    }
                }
                if ($selectedMultiplier == 1000) {
                    $requiredFor500 = max(0, (500 - 1) * $betAmount);
                    if ($jackpotBalance >= $requiredFor500) {

                        return 500;
                    }
                    $requiredFor250 = max(0, (250 - 1) * $betAmount);
                    if ($jackpotBalance >= $requiredFor250) {

                        return 250;
                    }
                }

                $mediumBalance = $this->getMediumWalletBalance();
                $requiredFor50 = max(0, (50 - 1) * $betAmount);
                if ($mediumBalance >= $requiredFor50) {

                    return 50;
                }

                return 0;
            }
        }

        return $selectedMultiplier;
    }

    protected function smartMultiplierSelect(
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
        int $betUnit = 0,
        int $poolBalance = 0,
        bool $hasPaidForJackpot = false,
        float $betAmount = 0
    ): int {
        $multipliers = $this->getAvailableMultipliers();
        $weights = [];
        $hasPaidForJackpot = $hasPaidForJackpot || ($unlockContribution > 0 && $contributionBank >= $unlockContribution);

        $highTierAvailability = $this->resolveHighTierAvailability(
            $betUnit,
            $globalVaultBalance,
            $isTopLossCandidate,
            $hasPaidForJackpot,
            $deviation,
            $pityCount
        );

        foreach ($multipliers as $m) {
            $baseWeight = $this->getNaturalWeight($m);
            $weight = $baseWeight;

            if ($forceMiniWins && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 2,
                    10 => $baseWeight * 1.5,
                    default => 0,
                };
            } elseif ($isDrainLocked && !$forceJackpot) {
                $weight = match ($m) {
                    5 => $baseWeight * 1.5,
                    10 => $baseWeight,
                    default => $baseWeight * 0.1,
                };
            } elseif ($forceJackpot) {
                if ($m < 250) {
                    $weight = 0;
                } elseif (!($highTierAvailability[$m] ?? false)) {
                    $weight = 0;
                } else {

                    $requiredPayout = max(0, ($m - 1) * $betAmount);
                    $jackpotBalance = $this->getJackpotWalletBalance();
                    $globalBalance = $this->getGlobalVaultBalance();
                    $totalAvailable = $jackpotBalance + $globalBalance;

                    if ($totalAvailable >= $requiredPayout) {

                        $weight = $baseWeight;
                    } else {

                        $weight = $baseWeight * 0.01;
                    }
                }
            } else {

                if ($m >= 250) {
                    if (!$hasPaidForJackpot && !$isTopLossCandidate) {
                        $progress = $unlockContribution > 0 ? ($contributionBank / $unlockContribution) : 0;
                        if ($progress < 0.75) {
                            $weight = 0;
                        }
                    }
                    if (!($highTierAvailability[$m] ?? false)) {
                        $weight = 0;
                    }
                }

                if (in_array($m, [5, 10, 20])) {

                    $deviationMultiplier = 1.0;

                    if ($deviation >= 0.1) {

                        $deviationMultiplier = 0.1;
                    } elseif ($deviation >= 0.05) {

                        $deviationMultiplier = 0.3;
                    } elseif ($deviation >= 0) {

                        $deviationMultiplier = 1.0;
                    } elseif ($deviation >= -0.1) {

                        $deviationMultiplier = 1.5;
                    } elseif ($deviation >= -0.2) {

                        $deviationMultiplier = 2.0;
                    } else {

                        $deviationMultiplier = 3.0;
                    }

                    $weight *= $deviationMultiplier;
                }

                if (in_array($m, [50, 70, 100]) && $weight > 0) {
                    $requiredPayout = max(0, $m * $betAmount);
                    $mediumBalance = $this->getMediumWalletBalance();

                    if ($mediumBalance >= $requiredPayout) {

                        $liquidityMultiplier = min(3.0, $mediumBalance / max(1, $requiredPayout));

                        if ($mediumBalance >= 5000) {
                            $weight *= 200.0;

                            if ($m == 50)
                                $weight *= 10.0;
                            if ($m == 70)
                                $weight *= 8.0;
                            if ($m == 100)
                                $weight *= 6.0;
                        } elseif ($mediumBalance >= 3000) {
                            $weight *= 100.0;
                        } elseif ($mediumBalance >= 1500) {
                            $weight *= 50.0;
                        } else {
                            $weight *= $liquidityMultiplier * 8.0;
                        }
                    } else {

                        $weight *= 0.01;
                    }
                }

                if (in_array($m, [250, 500, 1000]) && $weight > 0 && !$forceJackpot) {
                    $requiredPayout = max(0, ($m - 1) * $betAmount);
                    $jackpotBalance = $this->getJackpotWalletBalance();
                    $globalBalance = $this->getGlobalVaultBalance();
                    $totalAvailable = $jackpotBalance + $globalBalance;

                    if ($totalAvailable >= $requiredPayout && $hasPaidForJackpot) {

                        $weight *= ($jackpotBalance >= $requiredPayout) ? 3.0 : 2.0;
                    } else if (!$hasPaidForJackpot) {

                        $weight *= 0.01;
                    } else {

                        $weight = 0;
                    }
                }

                if ($weight > 0) {
                    if ($deviation >= 0.05) {
                        $weight *= 0.8;
                    } elseif ($deviation <= -0.15) {
                        $weight *= 1.2;
                    }

                    if ($streak >= 8) {
                        $weight *= 1.1;
                    }
                }
            }

            $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
        }

        $selectedMultiplier = $this->weightedRandom($multipliers, $weights);

        return $this->validateMultiplierAvailability($selectedMultiplier, $betAmount);
    }

    protected function getAvailableMultipliers(): array
    {
        return [5, 10, 20, 50, 70, 100, 250, 500, 1000];
    }

    protected function tweakMultiplierWeights(array $multipliers, array $weights, array $context): array
    {
        return $weights;
    }

    protected function resolveHighTierAvailability(
        int $betUnit,
        int $globalVaultBalance,
        bool $isTopLossCandidate,
        bool $hasPaidForJackpot,
        float $deviation,
        int $pityCount
    ): array {
        $tiers = [250, 500, 1000];
        $availability = [];

        foreach ($tiers as $tier) {
            $availability[$tier] = $this->canDisburseHighMultiplier(
                $tier,
                $betUnit,
                $globalVaultBalance,
                $isTopLossCandidate,
                $hasPaidForJackpot,
                $deviation,
                $pityCount
            );
        }

        return $availability;
    }

    protected function shouldUseCautiousDistribution(
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

    protected function cautiousDistributionWeight(int $multiplier, float $baseWeight, int $streak): float
    {
        $streakPenalty = max(0.4, 1 - min(0.6, $streak * 0.04));

        return match ($multiplier) {
            5 => $baseWeight * $streakPenalty,
            10 => $baseWeight * $streakPenalty,
            20 => $baseWeight * $streakPenalty,
            50 => $baseWeight * $streakPenalty,
            70 => $baseWeight * $streakPenalty,
            default => $baseWeight * 0.1,
        };
    }

    protected function neutralSmallWinWeight(
        int $multiplier,
        float $baseWeight,
        float $chaos,
        int $streak,
        bool $isBeginner
    ): float {
        $streakBoost = 1 + min(0.5, $streak * 0.05);
        $beginnerBoost = $isBeginner ? 1.3 : 1.0;

        return match ($multiplier) {
            5 => $baseWeight * $chaos * $streakBoost * $beginnerBoost,
            10 => $baseWeight * $chaos * $beginnerBoost,
            20 => $baseWeight * $chaos,
            50 => $baseWeight * $chaos,
            70 => $baseWeight * $chaos,
            default => $baseWeight * 0.5 * $chaos,
        };
    }

    protected function ratingSensitiveWeight(
        int $multiplier,
        float $baseWeight,
        float $deviation,
        float $chaos,
        int $streak,
        float $jackpotScaling,
        int $pityCount,
        bool $isDrainLocked,
        bool $isBeginner,
        bool $isTopLossCandidate = false,
        array $highTierAvailability = [],
        float $poolWeight = 1.0
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

        if ($isTopLossCandidate && $multiplier >= 250) {
            $weight *= 1.35;
            if ($multiplier >= 500 && $pityCount > 900) {
                $weight *= 1.25;
            }
        }

        if ($multiplier === 1000 && $pityCount > 1100) {
            $weight *= 1.15;
        }

        $availableHighCount = count(array_filter([250, 500, 1000], fn($tier) => $highTierAvailability[$tier] ?? false));

        if ($availableHighCount > 1 && $multiplier === 250) {
            $weight *= max(0.65, 1 - 0.15 * ($availableHighCount - 1));
        }

        if ($multiplier >= 500 && ($highTierAvailability[$multiplier] ?? false)) {
            $weight *= (1 + min(0.6, $pityCount / 900)) * $poolWeight;
        }

        if ($multiplier === 1000 && ($highTierAvailability[1000] ?? false) && $pityCount > 950) {
            $weight *= 1.2;
        }

        return $weight;
    }

    protected function finalizeMultiplierWeight(float $weight, int $multiplier, ?HighMultiplierSignal $highMultiplierSignal): int
    {
        if ($weight <= 0) {
            return 0;
        }

        if ($highMultiplierSignal && $highMultiplierSignal->hasPriority($multiplier)) {
            $weight *= max(1.0, $highMultiplierSignal->weightFor($multiplier));
        }

        return (int) max(0, round($weight));
    }

    protected function getNaturalWeight(int $multiplier): int
    {

        return match ($multiplier) {
            5 => 2800,
            10 => 1800,
            20 => 1200,
            50 => 700,
            70 => 400,
            100 => 200,
            250 => 80,
            500 => 20,
            1000 => 5,
            default => 1,
        };
    }

    protected function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0)
            return $values[0];

        $random = rand(1, (int) $totalWeight);
        $currentWeight = 0;

        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }

        $availableValues = [];
        foreach ($values as $index => $value) {
            if ($weights[$index] > 0) {
                $availableValues[] = $value;
            }
        }

        return $availableValues ? $availableValues[array_rand($availableValues)] : $values[0];
    }

    private function getGlobalVaultBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
    }

    private function getMediumWalletBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET);
    }

    private function getJackpotWalletBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET);
    }

    private function decreaseMediumWallet(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }
        FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount);
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount, $description, $userId);
    }

    private function increaseGlobalVaultBalance(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, $description, $userId);
    }

    private function distributeHouseCut(int $totalAmount): void
    {
        if ($totalAmount <= 0) {
            return;
        }

        $globalVaultAmount = (int) round($totalAmount * 0.60);
        $jackpotAmount = (int) round($totalAmount * 0.20);
        $mediumAmount = (int) round($totalAmount * 0.10);

        if ($globalVaultAmount > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $globalVaultAmount);
        }
        if ($jackpotAmount > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpotAmount);
        }
        if ($mediumAmount > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $mediumAmount);
        }
    }

    private function decreaseGlobalVaultBalance(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, $description, $userId);
    }

    private function decreaseJackpotWalletBalance(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount);
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount, $description, $userId);
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

        $payoutPortion = max(0, ($multiplier - 1) * $betUnit);

        $jackpotBalance = $this->getJackpotWalletBalance();

        return $jackpotBalance >= $payoutPortion;
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

    private function distributeBetAmount(float $totalAmount): void
    {

        $globalVaultAmount = $totalAmount * 0.55;
        $globalVaultAmountInt = (int) round($globalVaultAmount);

        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO GLOBAL_VAULT', [
            'amount' => $globalVaultAmountInt,
            'percentage' => 0.55,
            'expected' => round($totalAmount * 0.55),
        ]);

        $this->increaseGlobalVaultBalance($globalVaultAmountInt, "Bet contribution (55%)", null);

        $jackpotWalletAmount = $totalAmount * 0.15;
        $jackpotWalletAmountInt = (int) round($jackpotWalletAmount);

        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO JACKPOT_WALLET', [
            'amount' => $jackpotWalletAmountInt,
            'percentage' => 0.15,
            'expected' => round($totalAmount * 0.15),
        ]);

        $this->increaseJackpotWallet($jackpotWalletAmountInt, "Bet contribution (15%)", null);

        $mediumWalletAmount = $totalAmount * 0.10;
        $mediumWalletAmountInt = (int) round($mediumWalletAmount);

        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO MEDIUM_WALLET', [
            'amount' => $mediumWalletAmountInt,
            'percentage' => 0.10,
            'expected' => round($totalAmount * 0.10),
        ]);

        $this->increaseMediumWallet($mediumWalletAmountInt, "Bet contribution (10%)", null);

        \Illuminate\Support\Facades\Log::debug('distributeBetAmount COMPLETED', [
            'total_amount' => $totalAmount,
            'total_distributed' => $globalVaultAmountInt + $jackpotWalletAmountInt + $mediumWalletAmountInt,
            'global_vault' => $globalVaultAmountInt,
            'jackpot_wallet' => $jackpotWalletAmountInt,
            'medium_wallet' => $mediumWalletAmountInt,
            'app_and_receiver_fees' => round($totalAmount * 0.20),
        ]);

    }

    private function increaseJackpotWallet(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount);
        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount, $description, $userId);
    }

    private function increaseMediumWallet(int $amount, ?string $description = null, ?int $userId = null): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount);
        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount, $description, $userId);
    }
}
