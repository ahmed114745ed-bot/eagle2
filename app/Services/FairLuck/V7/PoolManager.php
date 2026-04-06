<?php

namespace App\Services\FairLuck\V7;

use App\Models\CoreWallet;
use App\Models\FairLuckSetting;
use App\Models\FairLuckWallet;

class PoolManager
{

    public function getTotalBalance(): int
    {
        return FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
    }

    public function getWalletBalances(): array
    {
        $balance = FairLuckWallet::getRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT);
        return [
            'global_vault' => $balance,
            'medium_wallet' => 0,
            'jackpot_wallet' => 0,
        ];
    }


    /**
     * توزيع الرهان:
     * - كل مبلغ الرهان (100%) يدخل Global Vault مباشرة
     * - لا يُخصم شيء هنا - رسوم التطبيق تُخصم من المكسب عند الفوز فقط
     *
     * المنطق (الحل 3 - الأعدل):
     * - عند الخسارة: pool يكسب +100% من الرهان (لا رسوم)
     * - عند الفوز: pool يدفع المكسب كاملاً، ثم يُخصم appFee من المكسب ويُحوَّل لـ app_wallet
     * - Pool يبقى zero-sum تماماً على المدى البعيد
     * - app_wallet تكسب فقط من أرباح الفائزين (مثل الكازينوهات الحقيقية)
     */
    public function distributeBet(float $amount): void
    {
        if ($amount <= 0) return;

        $total = (int) round($amount);
        if ($total <= 0) return;

        // كل الرهان يدخل pool اللعب مباشرة (100%)
        FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $total);
        FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $total, 'V7 Bet contribution', null);
    }


    /**
     * دفع المكسب للفائز مع خصم رسوم التطبيق من المكسب:
     * - pool يدفع المكسب كاملاً (multiplier × betAmount)
     * - appFee = appFeeRate × payoutAmount تُخصم من المكسب وتذهب لـ app_wallet
     * - اللاعب يستلم: payoutAmount - appFee (صافي المكسب)
     *
     * @param int $amount المكسب الإجمالي (multiplier × betAmount)
     * @param int $multiplier المضاعف
     * @param int $userId معرف المستخدم
     * @return array ['paid' => bool, 'net_payout' => int, 'app_fee' => int]
     */
    public function payout(int $amount, int $multiplier, int $userId): array
    {
        if ($amount <= 0) return ['paid' => true, 'net_payout' => 0, 'app_fee' => 0];

        $totalBalance = $this->getTotalBalance();
        $negativeLimit = BankruptcyProtection::getNegativeLimit();

        // Check if we can afford this payout
        if (($totalBalance + $negativeLimit) < $amount) {
            return ['paid' => false, 'net_payout' => 0, 'app_fee' => 0];
        }

        // حساب رسوم التطبيق من المكسب
        $appFeeRate = FairLuckSetting::getByKey('fair_luck_owner_fee_rate', 0.10);
        $appFee = (int) round($amount * $appFeeRate);
        $netPayout = $amount - $appFee; // ما يستلمه اللاعب فعلاً

        $description = "V7 Win payout ({$multiplier}x)";

        // pool يدفع المكسب كاملاً
        FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, $description, $userId);

        // appFee تذهب لـ app_wallet من المكسب
        if ($appFee > 0) {
            $appWallet = CoreWallet::where('name', 'app_wallet')->first();
            if ($appWallet instanceof CoreWallet) {
                $appWallet->increment('coins', $appFee);
            }
        }

        return ['paid' => true, 'net_payout' => $netPayout, 'app_fee' => $appFee];
    }
}
