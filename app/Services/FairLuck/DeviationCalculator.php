<?php


namespace App\Services\FairLuck;

use App\Models\FairLuckSetting;

class DeviationCalculator
{
    // النسب المخصومة من كل رهان (محددة في FairLuckService3)
    private const APP_PROFIT_RATE = 0.10;      // 10% ربح التطبيق
    private const JACKPOT_WALLET_RATE = 0.10;  // 10% محفظة الجاكبوت  
    private const MEDIUM_WALLET_RATE = 0.15;   // 15% المحفظة المتوسطة
    
    /**
     * Calculate current deviation from target loss rate.
     * 
     * معامل الانحراف = (الخسارة المستهدفة - الخسارة الفعلية) / إجمالي الرهانات الصافية
     * 
     * القيمة الصافية للهدية = قيمة الرهان - النسب المخصومة (35%)
     * 
     * Negative deviation: User lost MORE than target (deficit) -> increase win chance
     * Positive deviation: User lost LESS than target (surplus) -> decrease win chance
     */
    public function calculate(float $totalBets, float $totalProfit): float
    {
        if ($totalBets <= 0) {
            return 0;
        }

        $totalDeductionRate = self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE;
        
        $netTotalBets = $totalBets * (1 - $totalDeductionRate);
        
        $targetLossRate = (float) FairLuckSetting::getByKey('target_loss_rate', 0.01);
        
        $actualLoss = -$totalProfit; 
        
        $targetLoss = $netTotalBets * $targetLossRate;

        return ($targetLoss - $actualLoss) / $netTotalBets;
    }
    
    /**
     * الحصول على النسب المخصومة
     */
    public static function getDeductionRates(): array
    {
        return [
            'app_profit_rate' => self::APP_PROFIT_RATE,
            'jackpot_wallet_rate' => self::JACKPOT_WALLET_RATE,
            'medium_wallet_rate' => self::MEDIUM_WALLET_RATE,
            'total_deduction_rate' => self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE,
            'net_gift_rate' => 1 - (self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE)
        ];
    }
    
    /**
     * حساب احتمالية الجاكبوت بناءً على الرصيد المتاح
     */
    public static function calculateJackpotProbability(
        float $baseJackpotProbability,
        float $betAmount,
        int $jackpotWalletBalance,
        int $globalVaultBalance,
        int $desiredMultiplier = 250
    ): float {
        $requiredPayout = max(0, ($desiredMultiplier - 1) * $betAmount);
        $totalAvailable = $jackpotWalletBalance + $globalVaultBalance;
        
        if ($totalAvailable <= 0 || $requiredPayout <= 0) {
            return 0;
        }
        
        // حساب نسبة التغطية (الرصيد المتاح / المبلغ المطلوب)
        $coverageRatio = min(1.0, $totalAvailable / $requiredPayout);
        
        // تعديل الاحتمالية بناءً على نسبة التغطية
        // إذا كان الرصيد 100% كافي، الاحتمالية كاملة
        // إذا كان الرصيد 50% كافي، الاحتمالية 50%
        return $baseJackpotProbability * $coverageRatio;
    }
    public static function calculateNetGiftValue(float $betAmount): float
    {
        $totalDeductionRate = self::APP_PROFIT_RATE + self::JACKPOT_WALLET_RATE + self::MEDIUM_WALLET_RATE;
        return $betAmount * (1 - $totalDeductionRate);
    }
}
