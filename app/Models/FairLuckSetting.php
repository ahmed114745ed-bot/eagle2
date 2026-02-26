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
}
