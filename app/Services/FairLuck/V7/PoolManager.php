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
     * - appFee = 10% من betAmount تذهب لـ app_wallet فوراً (من كل رميه)
     * - pool يستقبل: betAmount - appFee = 90%
     *
     * المنطق:
     * - عند الخسارة: pool يكسب 90% من الرهان
     * - عند الفوز: pool يدفع multiplier × betAmount كاملاً للمستخدم
     * - app_wallet تكسب 10% من كل رميه (ثابت)
     * - صافي ربح التطبيق = 10% - 2% (خسارة pool) = 8%
     * - RTP المستخدم = 92% ✅
     */
    public function distributeBet(float $amount): void
    {
        if ($amount <= 0) return;

        $total = (int) round($amount);
        if ($total <= 0) return;

        $appFeeRate = FairLuckSetting::getByKey('fair_luck_owner_fee_rate', 0.10);
        $appFee = (int) round($total * $appFeeRate);
        $appFeeRate = FairLuckSetting::getAppFeeRate();
        $receiverFeeRate = FairLuckSetting::getReceiverFeeRate();
        $appFee = (int) round($total * $appFeeRate);
        $receiverFee = (int) round($total * $receiverFeeRate);
        $netBetAmount = $total - $appFee - $receiverFee;
        $netBet = $netBetAmount; 

        if ($appFee > 0) {
            $appWallet = CoreWallet::where('name', 'app_wallet')->first();
            if ($appWallet instanceof CoreWallet) {
                $appWallet->increment('coins', $appFee);
            }
        }

        // net bet يدخل pool اللعب (90%)
        if ($netBet > 0) {
            FairLuckWallet::incrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $netBet);
            FairLuckWallet::increaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $netBet, 'V7 Bet contribution (net)', null);
        }
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

        // V7: لا رسوم على المكسب - المستخدم يستلم المكسب كاملاً
        // هذا يضمن أن RTP الفعلي للمستخدم = target_rtp (99%)
        // التطبيق يكسب من الخسائر فقط (pool يحتفظ بالرهانات الخاسرة)
        $appFee = 0;
        $receiverFee = 0; // لا رسوم على المكسب - المستخدم يستلم كامل المكسب
        $netPayout = $amount; // المستخدم يستلم المكسب كاملاً بدون خصومات

        $description = "V7 Win payout ({$multiplier}x)";

        // pool يدفع المكسب كاملاً للمستخدم
        FairLuckWallet::decrementRedisBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount);
        FairLuckWallet::decreaseBalance(FairLuckWallet::TYPE_GLOBAL_VAULT, $amount, $description, $userId);

        return ['paid' => true, 'net_payout' => $netPayout, 'app_fee' => $appFee];
    }
}
