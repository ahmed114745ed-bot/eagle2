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
        $value = (float) Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });

        if ($value <= 0) {
            throw new \Exception(__("Invalid rate for ':key'. Rate must be greater than zero.", ['key' => $key]));
        }

        return $value;
    }


    public static function getAppBaseRate(): float
    {
        if (self::getSystemMode() === 'legacy') {
            return self::getSetting('shipping_coins');
        }

        return self::getSetting('app_coin_rate');
    }

        public static function getAppBaseRate2(): float
    {
        if (self::getSystemMode() === 'legacy') {
            return self::getSetting('zones_coins');
        }

        return self::getSetting('app_coin_rate');
    }

    public static function getAppBaseRateOrUserCoins(): float
    {
        if (self::getSystemMode() === 'legacy') {
            return self::getSetting('user_coins');
        }

        return self::getUserTransferRate();
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

        // Priority 1: User Custom Rate (from AdminCoinRate table)
        $customRate = AdminCoinRate::where('admin_id', $admin->id)->value('rate');
        if ($customRate && (float) $customRate > 0) {
            return (float) $customRate;
        }

        // Priority 2: Role Default Rate (from settings table based on admin type/role)
        $roleRate = self::getRoleDefaultRate($admin);
        if ($roleRate && $roleRate > 0) {
            return $roleRate;
        }

        // Priority 3: Global App Rate (fallback)
        return self::getAppBaseRate();
    }

    /**
     * Get the default rate for an admin's role.
     * Checks for role-specific rate settings in the format: {role_type}_coin_rate
     * 
     * @param mixed $admin The admin user object
     * @return float|null The role default rate or null if not set
     */
    protected static function getRoleDefaultRate($admin): ?float
    {
        if (!$admin || !isset($admin->type)) {
            return null;
        }

        $roleType = $admin->type;
        
        // Map role types to their setting keys
        $roleSettingKeys = [
            'superadmin'    => 'super_admin_coin_rate',
            'super_admin'   => 'super_admin_coin_rate',
            'area-manager'  => 'area_manager_coin_rate',
            'area_manager'  => 'area_manager_coin_rate',
            'sub_admin'     => 'sub_admin_coin_rate',
            'bd'            => 'bd_coin_rate',
        ];

        $settingKey = $roleSettingKeys[$roleType] ?? null;
        
        if (!$settingKey) {
            return null;
        }

        // Try to get the role-specific rate from cache/settings
        $roleRate = Cache::rememberForever("setting_{$settingKey}", function () use ($settingKey) {
            return Setting::where('key', $settingKey)->value('value');
        });

        return $roleRate ? (float) $roleRate : null;
    }
}
