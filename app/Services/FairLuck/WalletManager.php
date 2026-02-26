<?php

namespace App\Services\FairLuck;

use App\Models\FairLuckWallet;
use Illuminate\Support\Facades\Log;

class WalletManager
{
    /**
     * الحصول على معلومات جميع المحافظ
     */
    public static function getAllWalletBalances(): array
    {
        return FairLuckWallet::getAllBalances();
    }

    /**
     * تحويل مبلغ من محفظة إلى أخرى
     */
    public static function transferBetweenWallets(string $fromWallet, string $toWallet, int $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $fromBalance = FairLuckWallet::getBalance($fromWallet);
        if ($fromBalance < $amount) {
            return false;
        }

        // خصم من المحفظة المصدر
        $decreaseSuccess = FairLuckWallet::decreaseBalance($fromWallet, $amount);
        if (!$decreaseSuccess) {
            return false;
        }

        // إضافة للمحفظة الهدف
        $increaseSuccess = FairLuckWallet::increaseBalance($toWallet, $amount);
        if (!$increaseSuccess) {
            // إعادة المبلغ للمحفظة المصدر في حالة الفشل
            FairLuckWallet::increaseBalance($fromWallet, $amount);
            return false;
        }

        Log::info("Wallet Transfer: {$amount} من {$fromWallet} إلى {$toWallet}");
        return true;
    }

    /**
     * إعادة توزيع الأرصدة بين المحافظ (لإعادة التوازن)
     */
    public static function rebalanceWallets(): void
    {
        $balances = self::getAllWalletBalances();
        $totalBalance = array_sum($balances);

        if ($totalBalance <= 0) {
            return;
        }

        // نسب التوزيع المحدثة
        $targetRatios = [
            FairLuckWallet::TYPE_GLOBAL_VAULT => 0.60,    // 60% للمحفظة الرئيسية الاقتصادية (5x,10x,20x)
            FairLuckWallet::TYPE_JACKPOT_WALLET => 0.20,  // 20% للجاكبوت (250x,500x,1000x)
            FairLuckWallet::TYPE_MEDIUM_WALLET => 0.10,   // 10% للمضاعفات المتوسطة (50x,70x,100x)
            // 10% ربح التطبيق لا يحتاج محفظة منفصلة
        ];

        foreach ($targetRatios as $walletType => $ratio) {
            $targetAmount = (int) round($totalBalance * $ratio);
            $currentAmount = $balances[$walletType] ?? 0;
            
            if ($currentAmount < $targetAmount) {
                $needed = $targetAmount - $currentAmount;
                // نقل من المحفظة الأكبر
                $largestWallet = array_keys($balances, max($balances))[0];
                if ($largestWallet !== $walletType && $balances[$largestWallet] > $needed) {
                    self::transferBetweenWallets($largestWallet, $walletType, $needed);
                }
            }
        }

        Log::info("Wallet Rebalancing completed", self::getAllWalletBalances());
    }

    /**
     * التحقق من صحة أرصدة المحافظ
     */
    public static function validateWalletIntegrity(): bool
    {
        $balances = self::getAllWalletBalances();
        
        foreach ($balances as $walletType => $balance) {
            if ($balance < 0) {
                Log::error("Negative wallet balance detected", [
                    'wallet_type' => $walletType,
                    'balance' => $balance
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * تسجيل تقرير حالة المحافظ
     */
    public static function logWalletStatus(): void
    {
        $balances = self::getAllWalletBalances();
        $total = array_sum($balances);
        
        $report = [
            'total_balance' => $total,
            'wallets' => $balances,
            // النسب ثابتة حسب التصميم وليست محسوبة من الأرصدة الحالية
            'percentages' => [
                'global_vault' => '60% (المحفظة الرئيسية الاقتصادية - للمضاعفات 5x,10x,20x)',
                'jackpot_wallet' => '20% (محفظة الجاكبوت - للمضاعفات 250x,500x,1000x)', 
                'medium_wallet' => '10% (المحفظة المتوسطة - للمضاعفات 50x,70x,100x)',
                'app_profit' => '10% (ربح التطبيق)',
            ],
        ];
        
        Log::info("FairLuck Wallets Status Report", $report);
    }
}