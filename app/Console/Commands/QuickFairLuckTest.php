<?php

namespace App\Console\Commands;

use App\Models\FairLuckWallet;
use App\Models\User;
use App\Models\Gift;
use App\Services\FairLuck\FairLuckService3;
use App\Services\FairLuck\DeviationCalculator;
use Illuminate\Console\Command;

class QuickFairLuckTest extends Command
{
    protected $signature = 'fairluck:quick-test 
                            {bet_amount=100 : مبلغ الرهان}
                            {--user=1 : معرف المستخدم}
                            {--gift=1 : معرف الهدية}
                            {--auto : تشغيل تلقائي بدون توقف}';

    protected $description = 'اختبار سريع لنظام FairLuck مع عرض النتائج المبسطة';

    public function handle()
    {
        $betAmount = (float) $this->argument('bet_amount');
        $userId = $this->option('user');
        $giftId = $this->option('gift');
        $auto = $this->option('auto');

        $user = User::find($userId);
        $gift = Gift::find($giftId);

        if (!$user || !$gift) {
            $this->error('❌ المستخدم أو الهدية غير موجودة');
            return;
        }

        $this->info("🎯 اختبار سريع - رهان: " . number_format($betAmount));
        $this->line('══════════════════════════════════════════════════════════════');

        $runNumber = 1;
        do {
            $this->runQuickTest($user, $gift, $betAmount, $runNumber);
            $runNumber++;
            
            if (!$auto) {
                $continue = $this->choice(
                    'هل تريد تشغيل اختبار آخر؟',
                    ['نعم', 'لا', 'تشغيل تلقائي'],
                    'نعم'
                );
                
                if ($continue === 'لا') break;
                if ($continue === 'تشغيل تلقائي') $auto = true;
            } else {
                sleep(1); // توقف قصير في الوضع التلقائي
            }
            
        } while (true);
    }

    private function runQuickTest($user, $gift, $betAmount, $runNumber)
    {
        $this->line('');
        $this->info("🎲 المحاولة #{$runNumber} - " . now()->format('H:i:s'));
        $this->line('──────────────────────────────────────────────────────────────');

        try {
            // قبل الرهان
            $balancesBefore = FairLuckWallet::getAllBalances();
            $userBalanceBefore = $user->di;
            
            // تنفيذ الرهان
            $fairLuckService = app(FairLuckService3::class);
            $result = $fairLuckService->processBet($user, $gift, $betAmount);
            
            // بعد الرهان
            $balancesAfter = FairLuckWallet::getAllBalances();
            $user->refresh();
            
            // عرض النتيجة المبسطة
            $this->showQuickResult($result, $betAmount, $balancesBefore, $balancesAfter, $userBalanceBefore, $user->di);
            
        } catch (\Exception $e) {
            $this->error("❌ خطأ: " . $e->getMessage());
        }
    }

    private function showQuickResult($result, $betAmount, $balancesBefore, $balancesAfter, $userBalanceBefore, $userBalanceAfter)
    {
        // الحالة الأساسية
        $status = $result->isWinner ? '🏆 فوز' : '💔 خسارة';
        $multiplier = $result->isWinner ? $result->multiplier . 'x' : '-';
        $profit = $result->profitAmount;
        
        // تحديد مصدر المكسب
        $source = '';
        if ($result->isWinner) {
            if ($result->multiplier >= 250) $source = ' 🎰';
            elseif ($result->multiplier >= 50) $source = ' 💰';
            else $source = ' 🎲';
        }

        // عرض النتيجة في سطر واحد
        $line = sprintf(
            "%s | %s%s | ربح: %s | انحراف: %s | مزاج: %s",
            $status,
            $multiplier,
            $source,
            number_format($profit),
            round($result->newDeviation, 3),
            $this->getMoodEmoji($result->mood)
        );
        
        $this->line($line);
        
        // تغيير الأرصدة
        $vaultChange = $balancesAfter['global_vault'] - $balancesBefore['global_vault'];
        $jackpotChange = $balancesAfter['jackpot_wallet'] - $balancesBefore['jackpot_wallet'];
        $mediumChange = $balancesAfter['medium_wallet'] - $balancesBefore['medium_wallet'];
        $userChange = $userBalanceAfter - $userBalanceBefore;
        
        $changesLine = sprintf(
            "تغييرات: مستخدم %s | رئيسي %s | جاكبوت %s | متوسط %s",
            $this->formatChange($userChange),
            $this->formatChange($vaultChange),
            $this->formatChange($jackpotChange),
            $this->formatChange($mediumChange)
        );
        
        $this->comment($changesLine);
        
        // عرض الأرصدة الحالية
        $balancesLine = sprintf(
            "أرصدة: رئيسي %s | جاكبوت %s | متوسط %s | إجمالي %s",
            number_format($balancesAfter['global_vault']),
            number_format($balancesAfter['jackpot_wallet']),
            number_format($balancesAfter['medium_wallet']),
            number_format(array_sum($balancesAfter))
        );
        
        $this->line($balancesLine);
    }

    private function getMoodEmoji($mood)
    {
        return match($mood) {
            'Generous' => '😊',
            'Recovery' => '😤',
            'Stable' => '😐',
            default => '🤔'
        };
    }

    private function formatChange($amount)
    {
        if ($amount > 0) {
            return '+' . number_format($amount);
        } elseif ($amount < 0) {
            return number_format($amount);
        } else {
            return '0';
        }
    }
}