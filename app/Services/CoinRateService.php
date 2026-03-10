<?php

namespace App\Services;

use App\Models\AdminCoinRate;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CoinRateService
{
    public static function getAppBaseRate()
    {
        return Cache::rememberForever('app_coin_rate', function () {
            return Setting::where('key', 'app_coin_rate')->first()?->value ?? 10000;
        });
    }

    public static function getUserTransferRate()
    {
        $enabled = Cache::rememberForever('user_transfer_rate_enabled', function () {
            return Setting::where('key', 'user_transfer_rate_enabled')->first()?->value == 1;
        });

        if (!$enabled) {
            return self::getAppBaseRate();
        }

        return Cache::rememberForever('user_transfer_coin_rate', function () {
            return Setting::where('key', 'user_transfer_coin_rate')->first()?->value ?? self::getAppBaseRate();
        });
    }

    public static function getAdminCustomRate($adminId)
    {
        $customRate = AdminCoinRate::where('admin_id', $adminId)->first();
        if ($customRate) {
            return $customRate->rate;
        }

        return self::getAppBaseRate();
    }

    public static function getEffectiveRate($admin = null)
    {
        if (!$admin) {
            return self::getAppBaseRate();
        }

        // Check if admin is one of the types that can have a custom rate
        // Usually, we check the role. For now, we'll check if a custom rate exists in the table.
        $customRate = AdminCoinRate::where('admin_id', $admin->id)->first();
        if ($customRate) {
            return $customRate->rate;
        }

        return self::getAppBaseRate();
    }
}
