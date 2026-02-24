<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use App\Services\FairLuck\ProfileManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestFairLuckService extends Command
{
    protected $signature = 'fairluck:test-service 
                            {user_id=1 : معرف المستخدم}
                            {gift_id=1 : معرف الهدية} 
                            {bet_amount=100 : مبلغ الرهان}
                            {--runs=1 : عدد مرات التشغيل}';

    protected $description = 'اختبار شامل لخدمة FairLuck مع عرض جميع التفاصيل';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $giftId = $this->argument('gift_id');
        $betAmount = (float) $this->argument('bet_amount');
        $runs = (int) $this->option('runs');

        $this->info('🎯 بدء اختبار نظام FairLuck الذكي');
        $this->line('══════════════════════════════════════════════════════════════');

        // التحقق من وجود المستخدم والهدية
        $user = User::find($userId);
        $gift = Gift::find($giftId);

        if (!$user) {
            $this->error("❌ المستخدم غير موجود: {$userId}");
            return;
        }

        if (!$gift) {
            $this->error("❌ الهدية غير موجودة: {$giftId}");
            return;
        }

        $this->showInitialState($user, $gift, $betAmount);

        // تشغيل الاختبارات
        for ($i = 1; $i <= $runs; $i++) {
            $this->line('');
            $this->info("🎲 المحاولة #{$i}:");
            $this->line('──────────────────────────────────────────────────────────────');
            
            $this->runSingleTest($user, $gift, $betAmount, $i);
            
            if ($i < $runs) {
                $this->line('');
                $this->ask('اضغط Enter للمتابعة للمحاولة التالية...');
            }
        }

        $this->line('');
        $this->info('✅ انتهى الاختبار بنجاح!');
    }

    private function showInitialState($user, $gift, $betAmount)
    {
        $this->line('');
        $this->info('📊 الحالة الأولية:');
        $this->table(
            ['البند', 'القيمة'],
            [
                ['المستخدم', $user->name . " (ID: {$user->id})"],
                ['رصيد المستخدم', number_format($user->di)],
                ['الهدية', $gift->name . " (ID: {$gift->id})"],
                ['مبلغ الرهان', number_format($betAmount)],
            ]
        );

        // عرض أرصدة المحافظ
        $balances = FairLuckWallet::getAllBalances();
        $total = array_sum($balances);
        
        $this->line('');
        $this->info('💰 أرصدة المحافظ:');
        $this->table(
            ['المحفظة', 'الرصيد', 'النسبة'],
            [
                ['المحفظة الرئيسية', number_format($balances['global_vault']), round(($balances['global_vault']/$total)*100, 1) . '%'],
                ['محفظة الجاكبوت', number_format($balances['jackpot_wallet']), round(($balances['jackpot_wallet']/$total)*100, 1) . '%'],
                ['المحفظة المتوسطة', number_format($balances['medium_wallet']), round(($balances['medium_wallet']/$total)*100, 1) . '%'],
                ['📈 الإجمالي', number_format($total), '100%']
            ]
        );
    }

    private function runSingleTest($user, $gift, $betAmount, $runNumber)
    {
        try {
            // الحالة قبل الرهان
            $this->showPreBetState($user, $betAmount);
            
            // تنفيذ الرهان
            $fairLuckService = app(FairLuckService3::class);
            $result = $fairLuckService->processBet($user, $gift, $betAmount);
            
            // عرض النتيجة
            $this->showBetResult($result, $betAmount);
            
            // الحالة بعد الرهان
            $this->showPostBetState($user);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ في المحاولة #{$runNumber}: " . $e->getMessage());
            $this->line("Stack trace: " . $e->getTraceAsString());
        }
    }

    private function showPreBetState($user, $betAmount)
    {
        // الحصول على البروفايل
        $profileManager = app(ProfileManager::class);
        $profile = $profileManager->getProfile($user->id);
        
        // حساب معامل الانحراف الحالي
        $calculator = new DeviationCalculator();
        $currentDeviation = $calculator->calculate(
            $profile->total_bets,
            $profile->total_profit,
            $profile->medium_wallet_wins ?? 0,
            $profile->jackpot_wallet_wins ?? 0
        );

        // النسب والمعدلات
        $rates = DeviationCalculator::getDeductionRates();
        $netGiftValue = DeviationCalculator::calculateNetGiftValue($betAmount);

        $this->info('🔍 الحالة قبل الرهان:');
        
        // بروفايل المستخدم
        $this->table(
            ['البيان', 'القيمة'],
            [
                ['إجمالي الرهانات السابقة', number_format($profile->total_bets)],
                ['إجمالي الربح/الخسارة', number_format($profile->total_profit)],
                ['مكاسب المحفظة المتوسطة', number_format($profile->medium_wallet_wins ?? 0)],
                ['مكاسب الجاكبوت', number_format($profile->jackpot_wallet_wins ?? 0)],
                ['معامل الانحراف الحالي', round($currentDeviation, 4)],
            ]
        );

        // تحليل الرهان الحالي
        $this->table(
            ['تحليل الرهان', 'القيمة'],
            [
                ['مبلغ الرهان', number_format($betAmount)],
                ['نسبة الخصم الإجمالية', ($rates['total_deduction_rate'] * 100) . '%'],
                ['المخصوم للتطبيق (10%)', number_format($betAmount * $rates['app_profit_rate'])],
                ['المخصوم للجاكبوت (10%)', number_format($betAmount * $rates['jackpot_wallet_rate'])],
                ['المخصوم للمتوسط (15%)', number_format($betAmount * $rates['medium_wallet_rate'])],
                ['القيمة الصافية للحساب', number_format($netGiftValue)],
            ]
        );

        // حساب احتماليات الجاكبوت المختلفة
        $balances = FairLuckWallet::getAllBalances();
        $baseJackpotProb = 0.10;
        
        $jackpotProbs = [];
        foreach ([250, 500, 1000] as $multiplier) {
            $prob = DeviationCalculator::calculateJackpotProbability(
                $baseJackpotProb,
                $betAmount,
                $balances['jackpot_wallet'],
                $balances['global_vault'],
                $multiplier
            );
            $jackpotProbs[] = [
                "جاكبوت {$multiplier}x",
                round($prob * 100, 2) . '%',
                number_format(($multiplier - 1) * $betAmount) . ' (مطلوب)',
                number_format($balances['jackpot_wallet'] + $balances['global_vault']) . ' (متاح)'
            ];
        }

        $this->table(
            ['نوع الجاكبوت', 'الاحتمالية', 'المبلغ المطلوب', 'الرصيد المتاح'],
            $jackpotProbs
        );
    }

    private function showBetResult($result, $betAmount)
    {
        $this->info('🎯 نتيجة الرهان:');
        
        $status = $result->isWinner ? '🏆 فائز' : '💔 خاسر';
        $multiplier = $result->isWinner ? $result->multiplier . 'x' : 'لا يوجد';
        $profit = $result->profitAmount;
        $mood = $result->mood;
        
        // تحديد مصدر المكسب
        $winSource = 'لا يوجد';
        if ($result->isWinner && $result->multiplier >= 250) {
            $winSource = 'محفظة الجاكبوت 🎰';
        } elseif ($result->isWinner && $result->multiplier >= 50 && $result->multiplier <= 100) {
            $winSource = 'المحفظة المتوسطة 💰';
        } elseif ($result->isWinner) {
            $winSource = 'النظام العادي 🎲';
        }

        $this->table(
            ['البيان', 'القيمة'],
            [
                ['الحالة', $status],
                ['المضاعف', $multiplier],
                ['مبلغ الربح/الخسارة', number_format($profit)],
                ['مصدر المكسب', $winSource],
                ['معامل الانحراف الجديد', round($result->newDeviation, 4)],
                ['مزاج النظام', $mood],
                ['خصم البيت', number_format($result->houseCut)],
            ]
        );

        // تفاصيل إضافية للفائزين
        if ($result->isWinner) {
            $totalPayout = $result->multiplier * $betAmount;
            $netProfit = $totalPayout - $betAmount;
            
            $this->table(
                ['تفاصيل المكسب', 'القيمة'],
                [
                    ['مبلغ الرهان', number_format($betAmount)],
                    ['المضاعف', $result->multiplier . 'x'],
                    ['إجمالي العائد', number_format($totalPayout)],
                    ['صافي الربح', number_format($netProfit)],
                ]
            );
        }
    }

    private function showPostBetState($user)
    {
        // تحديث بيانات المستخدم
        $user->refresh();
        
        // الحصول على البروفايل المحدث
        $profileManager = app(ProfileManager::class);
        $profile = $profileManager->getProfile($user->id);
        
        // أرصدة المحافظ المحدثة
        $balances = FairLuckWallet::getAllBalances();
        $total = array_sum($balances);

        $this->info('📈 الحالة بعد الرهان:');
        
        // البروفايل المحدث
        $this->table(
            ['بروفايل المستخدم', 'القيمة'],
            [
                ['إجمالي الرهانات', number_format($profile->total_bets)],
                ['إجمالي الربح/الخسارة', number_format($profile->total_profit)],
                ['مكاسب المحفظة المتوسطة', number_format($profile->medium_wallet_wins ?? 0)],
                ['مكاسب الجاكبوت', number_format($profile->jackpot_wallet_wins ?? 0)],
                ['رصيد المستخدم الحالي', number_format($user->di)],
            ]
        );

        // أرصدة المحافظ المحدثة
        $this->table(
            ['المحفظة', 'الرصيد الجديد', 'النسبة'],
            [
                ['المحفظة الرئيسية', number_format($balances['global_vault']), round(($balances['global_vault']/$total)*100, 1) . '%'],
                ['محفظة الجاكبوت', number_format($balances['jackpot_wallet']), round(($balances['jackpot_wallet']/$total)*100, 1) . '%'],
                ['المحفظة المتوسطة', number_format($balances['medium_wallet']), round(($balances['medium_wallet']/$total)*100, 1) . '%'],
                ['📈 الإجمالي', number_format($total), '100%']
            ]
        );
    }
}