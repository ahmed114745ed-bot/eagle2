<?php

namespace App\Services;

use App\Models\AdminCoinRate;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CoinRateService
{
    public static function getSystemMode(): string
    {
        return Cache::rememberForever('coin_rate_system_mode', function () {
            return Setting::where('key', 'coin_rate_system_mode')->value('value') ?? 'unified';
        });
    }

    protected static function getSetting(string $key, $default = 10000): float
    {
        return (float) Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }


    public static function getAppBaseRate(): float
    {
        if (self::getSystemMode() === 'legacy') {
            return self::getSetting('zones_coins');
        }

        return self::getSetting('app_coin_rate');
    }

  
    public static function getUserTransferRate(): float
    {
        if (self::getSystemMode() === 'legacy') {
            return self::getSetting('user_coins', self::getAppBaseRate());
        }

        $enabled = Cache::rememberForever('user_transfer_rate_enabled', function () {
            return Setting::where('key', 'user_transfer_rate_enabled')->value('value') == 1;
        });

        if (!$enabled) {
            return self::getAppBaseRate();
        }

        return self::getSetting('user_transfer_coin_rate', self::getAppBaseRate());
    }

    public static function getAdminCustomRate(int $adminId): float
    {
        $rate = AdminCoinRate::where('admin_id', $adminId)->value('rate');
        
        return $rate ? (float) $rate : self::getAppBaseRate();
    }

    public static function getEffectiveRate($admin = null): float
    {
        $admin = $admin ?: self::resolveCurrentAdmin();

        if (self::getSystemMode() === 'legacy') {
            return self::getLegacyRate($admin);
        }

        return self::getUnifiedRate($admin);
    }

    protected static function resolveCurrentAdmin()
    {
        $admin = auth('admin')->user();
        
        if (!$admin && class_exists('\Encore\Admin\Facades\Admin')) {
            $admin = \Encore\Admin\Facades\Admin::user();
        }

        return $admin;
    }

    protected static function getLegacyRate($admin): float
    {
        if (!$admin) {
            return self::getSetting('zones_coins');
        }

        return match ($admin->type) {
            'superadmin'   => self::getSetting('super_admin_coins'),
            'area-manager' => self::getSetting('zones_coins'),
            default        => self::getSetting('zones_coins'),
        };
    }

    protected static function getUnifiedRate($admin): float
    {
        if (!$admin) {
            return self::getAppBaseRate();
        }

        $customRate = AdminCoinRate::where('admin_id', $admin->id)->value('rate');

        return $customRate ? (float) $customRate : self::getAppBaseRate();
    }
}
