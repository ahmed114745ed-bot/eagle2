<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FairLuckSetting extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = ['key', 'value', 'description'];

    protected static function booted()
    {
        static::updated(function () {
            Cache::forget('fair_luck:settings');
        });
    }

    public static function getByKey(string $key, $default = null)
    {
        $settings = Cache::remember('fair_luck:settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        if (!isset($settings[$key])) {
            return $default;
        }

        $value = $settings[$key];

        // Basic JSON detection
        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            return json_decode($value, true);
        }

        return $value;
    }

    public static function getAppFeeRate(): float
    {
        return (float) static::getByKey('fair_luck_app_fee_rate', 0.10);
    }

    public static function getReceiverFeeRate(): float
    {
        return (float) static::getByKey('fair_luck_receiver_fee_rate', 0.10);
    }

    public static function getNegativeLimit(): int
    {
        return (int) static::getByKey('global_vault_negative_limit', 30000);
    }
    public static function getOwnerFeeRate(): float
    {
        return (float) static::getByKey('fair_luck_owner_fee_rate', 0.10);
    }

    /**
     * Get Coin to USD conversion rate
     */
    public static function getCoinToUsdRate(): float
    {
        return (float) static::getByKey('coin_to_usd_rate', 0.01);
    }

    /**
     * Convert USD amount to coins
     */
    public static function usdToCoins(float $usdAmount): int
    {
        $rate = self::getCoinToUsdRate();
        return (int) round($usdAmount / $rate);
    }

    /**
     * Convert coins to USD amount
     */
    public static function coinsToUsd(int $coins): float
    {
        $rate = self::getCoinToUsdRate();
        return $coins * $rate;
    }

    /**
     * Get wallet protection thresholds in USD
     */
    public static function getHealthyWalletUsd(): float
    {
        return (float) static::getByKey('wallet_healthy_usd', 1000.00);
    }

    public static function getWarningWalletUsd(): float
    {
        return (float) static::getByKey('wallet_warning_usd', 500.00);
    }

    public static function getCriticalWalletUsd(): float
    {
        return (float) static::getByKey('wallet_critical_usd', 200.00);
    }

    public static function getMaxNegativeWalletUsd(): float
    {
        return (float) static::getByKey('wallet_max_negative_usd', 300.00);
    }

    /**
     * Get V7 Target RTP (default 92%)
     */
    public static function getTargetRTP(): float
    {
        return (float) static::getByKey('V7_target_rtp', 0.92);
    }

    /**
     * Get multiplier weights (flattened distribution)
     */
    public static function getMultiplierWeights(): array
    {
        $defaultWeights = [
            5 => 500,
            10 => 500,
            20 => 500,
            50 => 500,
            100 => 500,
            250 => 400,
            500 => 300,
            1000 => 200,
        ];
        
        $stored = static::getByKey('V7_multiplier_weights', null);
        if ($stored === null) {
            return $defaultWeights;
        }
        
        // Use + operator instead of array_merge to preserve numeric keys
        // array_merge reindexes numeric keys which breaks our multiplier keys!
        return $stored + $defaultWeights;
    }

    /**
     * Get wallet-based max multipliers for different health levels
     */
    public static function getWalletHealthyMaxMultiplier(): int
    {
        return (int) static::getByKey('V7_wallet_healthy_max_mult', 1000);
    }

    public static function getWalletModerateMaxMultiplier(): int
    {
        return (int) static::getByKey('V7_wallet_moderate_max_mult', 100);
    }

    public static function getWalletLowMaxMultiplier(): int
    {
        return (int) static::getByKey('V7_wallet_low_max_mult', 50);
    }

    public static function getWalletCriticalMaxMultiplier(): int
    {
        return (int) static::getByKey('V7_wallet_critical_max_mult', 20);
    }

    /**
     * Get minimum probability factor when wallet is low (default 60%)
     */
    public static function getMinProbabilityWhenLow(): float
    {
        return (float) static::getByKey('V7_min_prob_when_low', 0.60);
    }

    /**
     * Get bet gate thresholds for high multipliers
     */
    public static function getMinBetsFor100x(): int
    {
        return (int) static::getByKey('V7_min_bets_100x', 30);
    }

    public static function getMinBetsFor500x(): int
    {
        return (int) static::getByKey('V7_min_bets_500x', 100);
    }

    /**
     * Get max single win as percentage of wallet (default 15%)
     */
    public static function getMaxSingleWinPercentage(): float
    {
        return (float) static::getByKey('V7_max_single_win_pct', 0.15);
    }

    /**
     * Get maximum consecutive losses before forced win (default 30)
     */
    public static function getMaxLossStreak(): int
    {
        return (int) static::getByKey('V7_max_loss_streak', 30);
    }

    /**
     * Get multiplier for forced win after max loss streak (default 5x)
     */
    public static function getLossStreakForcedMultiplier(): int
    {
        return (int) static::getByKey('V7_loss_streak_forced_mult', 5);
    }
}
