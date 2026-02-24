<?php

namespace App\Console\Commands;

use App\Services\FairLuck\WalletManager;
use Illuminate\Console\Command;

class FairLuckWalletMaintenance extends Command
{
    protected $signature = 'fairluck:wallet-maintenance 
                            {action : الإجراء المطلوب (status|rebalance|validate)}
                            {--force : إجبار العملية بدون تأكيد}';

    protected $description = 'صيانة ومراقبة محافظ FairLuck';

    public function handle()
    {
        $action = $this->argument('action');
        $force = $this->option('force');

        match($action) {
            'status' => $this->showStatus(),
            'rebalance' => $this->rebalanceWallets($force),
            'validate' => $this->validateIntegrity(),
            default => $this->error("إجراء غير صحيح: {$action}")
        };
    }

    protected function showStatus()
    {
        $this->info('📊 حالة محافظ FairLuck:');
        $this->line('');

        $balances = WalletManager::getAllWalletBalances();
        $total = array_sum($balances);

        if ($total <= 0) {
            $this->warn('⚠️  جميع المحافظ فارغة!');
            return;
        }

        $this->table(
            ['المحفظة', 'الرصيد', 'النسبة'],
            [
                ['المحفظة الرئيسية', number_format($balances['global_vault']), round(($balances['global_vault'] / $total) * 100, 1) . '%'],
                ['محفظة الجاكبوت', number_format($balances['jackpot_wallet']), round(($balances['jackpot_wallet'] / $total) * 100, 1) . '%'],
                ['المحفظة المتوسطة', number_format($balances['medium_wallet']), round(($balances['medium_wallet'] / $total) * 100, 1) . '%'],
                ['📈 الإجمالي', number_format($total), '100%']
            ]
        );

        // تسجيل الحالة في اللوجز
        WalletManager::logWalletStatus();
    }

    protected function rebalanceWallets($force)
    {
        $this->info('⚖️  إعادة توزيع المحافظ...');

        $balancesBefore = WalletManager::getAllWalletBalances();
        $totalBefore = array_sum($balancesBefore);

        if ($totalBefore <= 0) {
            $this->warn('⚠️  لا يمكن إعادة التوزيع - جميع المحافظ فارغة!');
            return;
        }

        if (!$force) {
            $this->line('الأرصدة الحالية:');
            foreach ($balancesBefore as $wallet => $balance) {
                $this->line("  {$wallet}: " . number_format($balance));
            }

            if (!$this->confirm('هل تريد المتابعة؟')) {
                $this->info('تم إلغاء العملية.');
                return;
            }
        }

        WalletManager::rebalanceWallets();
        
        $balancesAfter = WalletManager::getAllWalletBalances();
        
        $this->line('');
        $this->info('✅ تم إعادة التوزيع بنجاح!');
        
        $this->table(
            ['المحفظة', 'قبل', 'بعد', 'التغيير'],
            [
                [
                    'المحفظة الرئيسية',
                    number_format($balancesBefore['global_vault']),
                    number_format($balancesAfter['global_vault']),
                    number_format($balancesAfter['global_vault'] - $balancesBefore['global_vault'])
                ],
                [
                    'محفظة الجاكبوت',
                    number_format($balancesBefore['jackpot_wallet']),
                    number_format($balancesAfter['jackpot_wallet']),
                    number_format($balancesAfter['jackpot_wallet'] - $balancesBefore['jackpot_wallet'])
                ],
                [
                    'المحفظة المتوسطة',
                    number_format($balancesBefore['medium_wallet']),
                    number_format($balancesAfter['medium_wallet']),
                    number_format($balancesAfter['medium_wallet'] - $balancesBefore['medium_wallet'])
                ]
            ]
        );
    }

    protected function validateIntegrity()
    {
        $this->info('🔍 فحص سلامة المحافظ...');

        $isValid = WalletManager::validateWalletIntegrity();
        
        if ($isValid) {
            $this->info('✅ جميع المحافظ سليمة!');
        } else {
            $this->error('❌ تم اكتشاف مشاكل في المحافظ!');
            $this->warn('يرجى مراجعة اللوجز للمزيد من التفاصيل.');
        }

        $balances = WalletManager::getAllWalletBalances();
        foreach ($balances as $wallet => $balance) {
            $status = $balance >= 0 ? '✅' : '❌';
            $this->line("  {$status} {$wallet}: " . number_format($balance));
        }
    }
}