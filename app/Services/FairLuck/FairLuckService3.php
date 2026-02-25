<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckTransaction;
use App\Models\FairLuckWallet;
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
 * 
 * === نسب المضاعفات المحسنة ===
 * 5x = 28% | 10x = 18% | 20x = 12% | 50x = 7% | 70x = 4%
 * 100x = 2% | 250x = 0.8% | 500x = 0.2% | 1000x = 0.05%
 * إجمالي نسبة الفوز: ~72% (RTP محسن لأكثر من 80%)
 * - 10% → ربح التطبيق (المحفظة الرئيسية)
 * - 20% → محفظة الجاكبوت (للمضاعفات ≥250x)
 * - 10% → المحفظة المتوسطة (للمضاعفات 50x-100x)
 * - 60% → باقي النظام (للعمليات العادية)
 * 
 * === حساب معامل الانحراف الجديد ===
 * معامل الانحراف = (الخسارة المستهدفة - الخسارة الفعلية) / القيمة الصافية للرهانات
 * القيمة الصافية = إجمالي الرهانات - النسب المخصومة (35%)
 * 
 * هذا النظام يضمن حساب معامل الانحراف بناءً على القيمة الفعلية للهدية
 * بعد خصم نسب المحافظ المختلفة.
 */
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
            // Default target for stability - تحسين RTP الأساسي
            $targetLossRate = 0.15; // تقليل معدل الخسارة الافتراضي لتحسين RTP إلى ~85%

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
                // Post-jackpot or persistent winner drain
                $targetLossRate = max($targetLossRate, 0.45); 
            }

            if ($isDrainLocked) {
                $targetLossRate = max($targetLossRate, 0.70); // تقليل العقوبة
            }
            
            $targetRTP = (1.0 - $targetLossRate);
            $localTargetRTP = $targetRTP; 
            
            // Adaptive scaling based on deviation (lifetime performance)
            if ($deviation >= 0.05) {
                // EXTREME recovery if player is in profit > 5%
                $localTargetRTP = 0.10 * $chaosFactor; // زيادة الحد الأدنى
            } elseif ($deviation >= 0) {
                $localTargetRTP = 0.40 * $chaosFactor; // تحسين للاعبين في الربح
            } elseif ($deviation < -0.15) {
                // أكثر سخاء للاعبين الخاسرين
                $localTargetRTP = 0.92 * $chaosFactor;
            }

            if ($isDrainLocked) {
                $localTargetRTP = min($localTargetRTP, 0.15 * $chaosFactor); // تحسين حتى مع القفل
            }

            $expectedMultiplier = $this->multiplierSelector->getExpectedMultiplier();
            $baseProb = ($localTargetRTP / $expectedMultiplier);

            $protectionMultiplier = $this->beginnerProtection->getMultiplier($profile);
            $finalProbability = $this->probabilityEngine->calculate($baseProb, $deviation, $protectionMultiplier);
            
            // Forced Win Loop: تقليل الحد الأقصى للخسائر المتتالية لتحسين RTP
            if ($consecutiveLosses >= 20) { // تقليل من 50 إلى 25
                $finalProbability = 1.0; // فوز مضمون بعد 25 خسارة
            } elseif ($consecutiveLosses >= 15) { // إضافة مستوى وسطي
                $finalProbability = max($finalProbability, 0.60); // زيادة احتمالية الفوز
            } elseif ($consecutiveLosses >= 8) { // تحسين مبكر
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
            
            // Targeted Jackpot Guarantee: تعتمد على الحظ والرصيد المتاح
            $forceJackpot = false;
            if (!$isDrainLocked) {
                $nearBankrupt = $user->di <= ($betAmount * 20);
                
                // حساب احتمالية الجاكبوت بناءً على الرصيد المتاح
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
                    if ($adjustedJackpotProb >= 0.05) { // فقط إذا كانت الاحتمالية معقولة
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
                
                // الحالات الحرجة (بغض النظر عن الرصيد)
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
            if ($isWinner || $forceWin) { // إضافة الفوز الإجباري في حالة 15 خسارة متتالية
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
                    $betUnit,
                    $poolBalance,
                    $hasPaidForJackpot,
                    $betAmount // إضافة قيمة الرهان للتحقق من السيولة
                );
                
                \Illuminate\Support\Facades\Redis::del($lossKey);
                if ($multiplier >= 250) {
                    $jackpotPayout = max(0, $multiplier * $betAmount); // المضاعف الكامل
                    \Illuminate\Support\Facades\Redis::del($jackpotKey);
                    $postJackpotDebt = $unlockContribution > 0 ? $unlockContribution : $betAmount;
                    \Illuminate\Support\Facades\Redis::set($contributionKey, -$postJackpotDebt);
                    \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200);
                    \Illuminate\Support\Facades\Redis::setex($drainLockKey, 86400, 1);
                    
                    $jackpotWalletBalance = $this->getJackpotWalletBalance();
                    if ($jackpotWalletBalance >= $jackpotPayout) {
                        $this->decreaseJackpotWalletBalance((int) round($jackpotPayout));
                    } else {
                        // إذا لم تكن محفظة الجاكبوت كافية، لا ندفع أي شيء
                        // هذا لا يجب أن يحدث لأن validateMultiplierAvailability يجب أن يمنع ذلك
                        $newDeviation = $this->deviationCalculator->calculate(
                            $profile->total_bets + $betAmount,
                            $profile->total_profit - $betAmount,
                            $betAmount
                        );
                        
                        return (object) [
                            'isWinner' => false,
                            'multiplier' => 0,
                            'profitAmount' => -$betAmount,
                            'houseCut' => $betAmount,
                            'balance' => $user->di - $betAmount,
                            'newDeviation' => $newDeviation
                        ];
                    }
                    
                    $this->lossLedger->markHighMultiplierAwarded($user->id, (int) round($jackpotPayout));
                } else {
                    \Illuminate\Support\Facades\Redis::incr($jackpotKey);
                    
                    $profit = max(0, $multiplier * $betAmount); // المضاعف الكامل بدون طرح الرهان
                    
                    // السحب من المحفظة المتوسطة للمضاعفات 50, 70, 100 - بدون شروط إضافية
                    if (in_array($multiplier, [50, 70, 100])) {
                        $mediumWalletBalance = $this->getMediumWalletBalance();
                        if ($mediumWalletBalance >= $profit) {
                            $this->decreaseMediumWallet((int) round($profit));
                            // لا نخصم أي شيء من المكاسب - اللاعب يستحق كامل مضاعفه
                        } else {
                            // إذا لم تكن المحفظة المتوسطة كافية، لا ندفع أي شيء
                            $newDeviation = $this->deviationCalculator->calculate(
                                $profile->total_bets + $betAmount,
                                $profile->total_profit - $betAmount,
                                $betAmount
                            );
                            
                            return (object) [
                                'isWinner' => false,
                                'multiplier' => 0,
                                'profitAmount' => -$betAmount,
                                'houseCut' => $betAmount,
                                'balance' => $user->di - $betAmount,
                                'newDeviation' => $newDeviation
                            ];
                        }
                    } else {
                        // للمضاعفات الصغيرة (5x, 10x, 20x) - السحب من المحفظة الرئيسية مباشرة
                        if (in_array($multiplier, [5, 10, 20])) {
                            $globalVaultBalance = $this->getGlobalVaultBalance();
                            if ($globalVaultBalance >= $profit) {
                                $this->decreaseGlobalVaultBalance((int) round($profit));
                                // لا نخصم أي شيء من المكاسب - اللاعب يستحق كامل مضاعفه
                            } else {
                                // إذا لم تكن المحفظة الرئيسية كافية، ندفع من المتاح ونسجل النقص
                                if ($globalVaultBalance > 0) {
                                    $this->decreaseGlobalVaultBalance($globalVaultBalance);
                                }
                                // تطبيق منطق contribution للباقي إذا كان متوفراً
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
                $updatedBank = min($contributionBank + $betAmount, $requiredContribution * 5);
                \Illuminate\Support\Facades\Redis::set($contributionKey, (int) round($updatedBank));
                \Illuminate\Support\Facades\Redis::expire($lossKey, 3600); 
                \Illuminate\Support\Facades\Redis::expire($jackpotKey, 86400); 
                \Illuminate\Support\Facades\Redis::expire($contributionKey, 259200); 
                
                // تم نقل التوزيع لأعلى في distributeBetAmount
                // $this->distributeLossAmount($betAmount);
                
                $this->lossLedger->addToGlobalPool((int) round($betAmount));
            }

            // لا نستخدم المحفظة الرئيسية للتسوية
            // $this->settleGlobalVaultBalance($isWinner, (int) $multiplier, $betAmount);

            // 6. Update Stats
            $profitAmount = $isWinner || $forceWin ? ($multiplier * $betAmount) : -$betAmount;
            
            // تحديث عداد الخسائر المتتالية
            if ($isWinner || $forceWin) {
                // إعادة تعيين عداد الخسائر المتتالية عند الفوز
                \Illuminate\Support\Facades\Redis::del("fairluck:consecutive_losses:{$user->id}");
            } else {
                // زيادة عداد الخسائر المتتالية
                \Illuminate\Support\Facades\Redis::incr("fairluck:consecutive_losses:{$user->id}");
                \Illuminate\Support\Facades\Redis::expire("fairluck:consecutive_losses:{$user->id}", 86400); // تنتهي بعد يوم
            }

            // تحديد مصدر المكسب وتسجيله
            $mediumWalletWin = 0;
            $jackpotWalletWin = 0;
            
            if ($isWinner && $multiplier > 1) {
                $winAmount = $multiplier * $betAmount; // المضاعف الكامل
                
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

            \Illuminate\Support\Facades\Log::info('FairLuckService3 BEFORE DISTRIBUTION', [
                'user_id' => $user->id,
                'bet_amount' => $betAmount,
                'global_vault_before' => $walletsBeforeDistribution['global_vault'],
                'jackpot_wallet_before' => $walletsBeforeDistribution['jackpot_wallet'],
                'medium_wallet_before' => $walletsBeforeDistribution['medium_wallet'],
            ]);

            $this->distributeBetAmount($betAmount);

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
                'expected_60_percent' => round($betAmount * 0.60),
                'expected_20_percent' => round($betAmount * 0.20),
                'expected_10_percent' => round($betAmount * 0.10),
            ]);

            $houseEdgeCut = $this->calculateHouseEdgeCut($betAmount, $isWinner, (int) $multiplier);
            // لا نحتاج لتوزيع houseEdgeCut إضافي لأن distributeBetAmount تتولى التوزيع الكامل

            $this->highMultiplierLedger->recordOutcome(
                $user->id,
                $betAmount,
                $profitAmount,
                $isWinner,
                $multiplier
            );
            
            $newDeviation = $this->deviationCalculator->calculate(
                $profile->total_bets + $betAmount,
                $profile->total_profit + $profitAmount,
                $profile->medium_wallet_wins ?? 0,
                $profile->jackpot_wallet_wins ?? 0
            );

            $this->profileManager->updateStats(
                $profile, 
                $betAmount, 
                $profitAmount, 
                $isWinner, 
                $newDeviation,
                $mediumWalletWin,
                $jackpotWalletWin
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
     * تحديد المضاعف المتاح بناءً على سيولة المحافظ
     * 
     * @param int $selectedMultiplier المضاعف المطلوب
     * @param float $betAmount قيمة الرهان
     * @return int المضاعف الفعلي المتاح
     */
    private function validateMultiplierAvailability(int $selectedMultiplier, float $betAmount): int
    {
        $requiredPayout = max(0, ($selectedMultiplier - 1) * $betAmount);
        
        // المضاعفات البسيطة (5, 10, 20) لا تحتاج تحقق من السيولة - تعتمد على الانحراف فقط
        if (in_array($selectedMultiplier, [5, 10, 20])) {
            return $selectedMultiplier;
        }
        
        // التحقق من المضاعفات المتوسطة (50, 70, 100)
        if (in_array($selectedMultiplier, [50, 70, 100])) {
            $mediumBalance = $this->getMediumWalletBalance();
            
            // تقليل متطلبات السيولة - اسمح بالمضاعفات حتى لو بسيولة أقل
            $minimumRequired = $requiredPayout * 0.2; // 20% فقط من المطلوب
            
            if ($mediumBalance >= $minimumRequired) {
                return $selectedMultiplier; // المضاعف متاح
            } else {
                // إذا لم تكن المحفظة المتوسطة كافية، جرب مضاعف أقل
                if ($selectedMultiplier == 100) return 70;
                if ($selectedMultiplier == 70) return 50;
                return $this->getFallbackMultiplier($betAmount);
            }
        }
        
        // التحقق من المضاعفات العالية (250, 500, 1000)
        if (in_array($selectedMultiplier, [250, 500, 1000])) {
            $jackpotBalance = $this->getJackpotWalletBalance();
            
            if ($jackpotBalance >= $requiredPayout) {
                return $selectedMultiplier; // المضاعف متاح
            } else {
                // إذا لم تكن محفظة الجاكبوت كافية، جرب مضاعف متوسط
                $mediumBalance = $this->getMediumWalletBalance();
                if ($mediumBalance > 1000) {
                    return 100; // تحويل لمضاعف متوسط
                }
                return $this->getFallbackMultiplier($betAmount);
            }
        }
        
        return $selectedMultiplier;
    }
    
    protected function getFallbackMultiplier(float $betAmount): int
    {
        // في حالة فشل المضاعف المحدد، اختر من المضاعفات البسيطة فقط
        $fallbackMultipliers = [5, 10, 20];
        return $fallbackMultipliers[array_rand($fallbackMultipliers)];
    }
    /**
     * Smart weight-based selection to keep user in suspense.
     */
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
        float $betAmount = 0 // قيمة الرهان للتحقق من السيولة
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

            // حالات خاصة لإجبار نتائج معينة
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
                    // فحص السيولة المتاحة للجاكبوت وإعطاء أولوية للمضاعفات المتاحة
                    $requiredPayout = max(0, ($m - 1) * $betAmount);
                    $jackpotBalance = $this->getJackpotWalletBalance();
                    $globalBalance = $this->getGlobalVaultBalance();
                    $totalAvailable = $jackpotBalance + $globalBalance;
                    
                    if ($totalAvailable >= $requiredPayout) {
                        // السيولة كافية، استخدم النسب الطبيعية
                        $weight = $baseWeight;
                    } else {
                        // السيولة غير كافية، قلل الوزن بشدة
                        $weight = $baseWeight * 0.01;
                    }
                }
            } else {
                // للمضاعفات العالية، تحقق من الشروط
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
                
                // المضاعفات البسيطة (5, 10, 20) تعتمد على معامل الانحراف - مع الحفاظ على النسب النسبية
                if (in_array($m, [5, 10, 20])) {
                    // معامل التعديل العام حسب الانحراف
                    $deviationMultiplier = 1.0;
                    
                    if ($deviation >= 0.1) {
                        // المستخدم في ربح كبير - قلل المضاعفات البسيطة
                        $deviationMultiplier = 0.1;
                    } elseif ($deviation >= 0.05) {
                        // المستخدم في ربح متوسط - قلل المضاعفات البسيطة
                        $deviationMultiplier = 0.3;
                    } elseif ($deviation >= 0) {
                        // المستخدم في ربح طفيف - حافظ على النسب الطبيعية
                        $deviationMultiplier = 1.0;
                    } elseif ($deviation >= -0.1) {
                        // المستخدم في خسارة طفيفة - زيد المضاعفات البسيطة
                        $deviationMultiplier = 1.5;
                    } elseif ($deviation >= -0.2) {
                        // المستخدم في خسارة متوسطة - زيد المضاعفات البسيطة أكثر
                        $deviationMultiplier = 2.0;
                    } else {
                        // المستخدم في خسارة كبيرة - أعطي أولوية عالية للمضاعفات البسيطة
                        $deviationMultiplier = 3.0;
                    }
                    
                    // تطبيق المعامل مع الحفاظ على النسب النسبية الأصلية
                    $weight *= $deviationMultiplier;
                }
                
                // للمضاعفات المتوسطة (50, 70, 100) - توزيع من المحفظة المتوسطة
                if (in_array($m, [50, 70, 100]) && $weight > 0) {
                    $requiredPayout = max(0, $m * $betAmount); // المضاعف الكامل
                    $mediumBalance = $this->getMediumWalletBalance();
                    
                    if ($mediumBalance >= $requiredPayout) {
                        // السيولة متوفرة في المحفظة المتوسطة، ارفع الوزن بشدة
                        $liquidityMultiplier = min(3.0, $mediumBalance / max(1, $requiredPayout));
                        
                        // عندما تصل المحفظة لأكثر من 5000، اجبر التوزيع بقوة هائلة!
                        if ($mediumBalance >= 5000) {
                            $weight *= 200.0; // تشجيع هائل للتفريغ الفوري
                            // اجعل المضاعفات المتوسطة تهيمن على الاختيار
                            if ($m == 50) $weight *= 10.0;  // 50x يصبح مهيمن
                            if ($m == 70) $weight *= 8.0;   // 70x قوي جداً
                            if ($m == 100) $weight *= 6.0;  // 100x قوي أيضاً
                        } elseif ($mediumBalance >= 3000) {
                            $weight *= 100.0;
                        } elseif ($mediumBalance >= 1500) {
                            $weight *= 50.0;
                        } else {
                            $weight *= $liquidityMultiplier * 8.0; // الحد الأدنى للتشجيع
                        }
                    } else {
                        // السيولة غير كافية
                        $weight *= 0.01;
                    }
                }
                
                // للمضاعفات العالية (250, 500, 1000) - توزيع من محفظة الجاكبوت
                if (in_array($m, [250, 500, 1000]) && $weight > 0 && !$forceJackpot) {
                    $requiredPayout = max(0, ($m - 1) * $betAmount);
                    $jackpotBalance = $this->getJackpotWalletBalance();
                    $globalBalance = $this->getGlobalVaultBalance();
                    $totalAvailable = $jackpotBalance + $globalBalance;
                    
                    if ($totalAvailable >= $requiredPayout && $hasPaidForJackpot) {
                        // السيولة متوفرة ودفع الشرط المطلوب، ارفع الوزن
                        $weight *= ($jackpotBalance >= $requiredPayout) ? 3.0 : 2.0;
                    } else if (!$hasPaidForJackpot) {
                        // لم يدفع الشرط المطلوب
                        $weight *= 0.01;
                    } else {
                        // السيولة غير كافية
                        $weight = 0;
                    }
                }
                
                // تطبيق تعديلات طفيفة بناءً على الحالة
                if ($weight > 0) {
                    if ($deviation >= 0.05) {
                        $weight *= 0.8; // تقليل الفرص للمستخدمين في ربح
                    } elseif ($deviation <= -0.15) {
                        $weight *= 1.2; // زيادة الفرص للمستخدمين في خسارة
                    }
                    
                    if ($streak >= 8) {
                        $weight *= 1.1; // زيادة طفيفة بعد سلسلة خسائر
                    }
                }
            }

            $weights[] = $this->finalizeMultiplierWeight($weight, $m, $highMultiplierSignal);
        }

        $selectedMultiplier = $this->weightedRandom($multipliers, $weights);
        
        // تحقق من توفر السيولة واتخذ قرار نهائي
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

        $availableHighCount = count(array_filter([250, 500, 1000], fn ($tier) => $highTierAvailability[$tier] ?? false));

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
        // نسب محسنة لتحسين RTP: 5x=28%, 10x=18%, 20x=12%, 50x=7%, 70x=4%, 100x=2%, 250x=0.8%, 500x=0.2%, 1000x=0.05%
        return match ($multiplier) {
            5 => 2800,    // 28%
            10 => 1800,   // 18%
            20 => 1200,   // 12%
            50 => 700,    // 7%
            70 => 400,    // 4%
            100 => 200,   // 2%
            250 => 80,    // 0.8%
            500 => 20,    // 0.2%
            1000 => 5,    // 0.05%
            default => 1,
        };
    }

    protected function weightedRandom(array $values, array $weights): int
    {
        $totalWeight = array_sum($weights);
        if ($totalWeight <= 0) return $values[0];
        
        $random = rand(1, (int)$totalWeight);
        $currentWeight = 0;
        
        foreach ($values as $index => $value) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $value;
            }
        }
        
        // احتياط - إذا لم يتم اختيار أي شيء، اختر عشوائياً من القائمة
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
        return FairLuckWallet::getBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
    }

    private function getMediumWalletBalance(): int
    {
        return FairLuckWallet::getBalance(FairLuckWallet::TYPE_MEDIUM_WALLET);
    }

    private function getJackpotWalletBalance(): int
    {
        return FairLuckWallet::getBalance(FairLuckWallet::TYPE_JACKPOT_WALLET);
    }

    private function decreaseMediumWallet(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount);
    }



    private function increaseGlobalVaultBalance(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
    }

    /**
     * توزيع الأموال بالنسب الجديدة لكل رمية
     * 60% للمحفظة الرئيسية، 20% للجاكبوت، 10% للمتوسطة، 10% ربح التطبيق
     */
    private function distributeHouseCut(int $totalAmount): void
    {
        if ($totalAmount <= 0) {
            return;
        }

        // توزيع بالنسب الجديدة
        $globalVaultAmount = (int) round($totalAmount * 0.60);  // 60% للمضاعفات الصغيرة
        $jackpotAmount = (int) round($totalAmount * 0.20);      // 20% للجاكبوت
        $mediumAmount = (int) round($totalAmount * 0.10);       // 10% للمتوسطة

        if ($globalVaultAmount > 0) {
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $globalVaultAmount);
        }
        if ($jackpotAmount > 0) {
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $jackpotAmount);
        }
        if ($mediumAmount > 0) {
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $mediumAmount);
        }
    }

    private function decreaseGlobalVaultBalance(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $balanceBefore = $this->getGlobalVaultBalance();
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
        $balanceAfter = $this->getGlobalVaultBalance();

        \Illuminate\Support\Facades\Log::debug('decreaseGlobalVaultBalance', [
            'table' => 'fair_luck_wallets',
            'wallet_type' => 'global_vault',
            'amount_decreased' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'decreased_by' => $balanceBefore - $balanceAfter,
            'success' => ($balanceBefore - $balanceAfter) === $amount,
        ]);
    }

    private function decreaseJackpotWalletBalance(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $balanceBefore = $this->getJackpotWalletBalance();
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount);
        $balanceAfter = $this->getJackpotWalletBalance();

        \Illuminate\Support\Facades\Log::debug('decreaseJackpotWalletBalance', [
            'table' => 'fair_luck_wallets',
            'wallet_type' => 'jackpot_wallet',
            'amount_decreased' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'decreased_by' => $balanceBefore - $balanceAfter,
            'success' => ($balanceBefore - $balanceAfter) === $amount,
        ]);
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

        // إزالة الشروط الإضافية - الاعتماد على السيولة فقط
        // if (!$hasPaidForJackpot && !$isTopLossCandidate) {
        //     return false;
        // }

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

    /**
     * توزيع مبلغ الخسارة على المحافظ المختلفة
     * 10% ربح التطبيق + 20% محفظة الجاكبوت + 10% المحفظة المتوسطة
     */
    /**
     * توزيع مبلغ الرهان بالنسب الجديدة لكل رمية (سواء فوز أو خسارة)
     * 60% للمحفظة الرئيسية، 20% للجاكبوت، 10% للمتوسطة، 10% ربح التطبيق
     */
    private function distributeBetAmount(float $betAmount): void
    {
        \Illuminate\Support\Facades\Log::debug('distributeBetAmount START', [
            'bet_amount' => $betAmount,
            'table_name' => 'fair_luck_wallets',
        ]);

        // 60% للمحفظة الرئيسية الاقتصادية (للمضاعفات الصغيرة)
        $globalVaultAmount = $betAmount * 0.60;
        $globalVaultAmountInt = (int) round($globalVaultAmount);
        
        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO GLOBAL_VAULT', [
            'amount' => $globalVaultAmountInt,
            'percentage' => 0.60,
            'expected' => round($betAmount * 0.60),
        ]);
        
        $this->increaseGlobalVaultBalance($globalVaultAmountInt);
        
        // 20% لمحفظة الجاكبوت 
        $jackpotWalletAmount = $betAmount * 0.20;
        $jackpotWalletAmountInt = (int) round($jackpotWalletAmount);
        
        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO JACKPOT_WALLET', [
            'amount' => $jackpotWalletAmountInt,
            'percentage' => 0.20,
            'expected' => round($betAmount * 0.20),
        ]);
        
        $this->increaseJackpotWallet($jackpotWalletAmountInt);
        
        // 10% للمحفظة المتوسطة
        $mediumWalletAmount = $betAmount * 0.10;
        $mediumWalletAmountInt = (int) round($mediumWalletAmount);
        
        \Illuminate\Support\Facades\Log::debug('DISTRIBUTING TO MEDIUM_WALLET', [
            'amount' => $mediumWalletAmountInt,
            'percentage' => 0.10,
            'expected' => round($betAmount * 0.10),
        ]);
        
        $this->increaseMediumWallet($mediumWalletAmountInt);
        
        \Illuminate\Support\Facades\Log::debug('distributeBetAmount COMPLETED', [
            'bet_amount' => $betAmount,
            'total_distributed' => $globalVaultAmountInt + $jackpotWalletAmountInt + $mediumWalletAmountInt,
            'global_vault' => $globalVaultAmountInt,
            'jackpot_wallet' => $jackpotWalletAmountInt,
            'medium_wallet' => $mediumWalletAmountInt,
            'remaining_app_profit' => round($betAmount * 0.10),
        ]);
        
        // 10% ربح التطبيق (لا يضاف للمحافظ - هو صافي ربح)
    }

    /**
     * زيادة رصيد محفظة الجاكبوت
     */
    private function increaseJackpotWallet(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_JACKPOT_WALLET, $amount);
    }

    /**
     * زيادة رصيد المحفظة المتوسطة
     */
    private function increaseMediumWallet(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_MEDIUM_WALLET, $amount);
    }
}
