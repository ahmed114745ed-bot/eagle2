<?php

namespace App\Observers;

use App\Helpers\CacheHelper;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingObserver
{
    public function saved(Setting $setting): void
    {
        $this->clearSettingCache($setting);
        $this->refreshCache();
    }

    public function deleted(Setting $setting): void
    {
        $this->clearSettingCache($setting);
        $this->refreshCache();
    }

    protected function clearSettingCache(Setting $setting): void
    {
        Cache::forget($setting->key);
        Cache::forget("settings.{$setting->key}");
    }

    protected function refreshCache(): void
    {
        Cache::forget('all_settings');

        $themeKeys = [
            'primary_color', 'secondary_color', 'text_primary_color',
            'text_secondary_color', 'box_background_color', 'app_background',
            'brand_background_image', 'table_background_color', 'dark_mode',
            'app_primary_color', 'background_color', 'bottom_nav_bottom_color',
            'bottom_nav_active_color', 'bottom_nav_inactive_color',
            'text_header_color', 'button_text_color', 'dark_mode_color',
            'light_mode_color',
        ];

        foreach ($themeKeys as $key) {
            Cache::forget($key);
        }

        Cache::forget('app_title');
        Cache::forget('app_title_en');
        Cache::forget('app_title_ar');
        Cache::forget('settings.app_title_en');
        Cache::forget('settings.app_title_ar');
        Cache::forget('appLogo');
        Cache::forget('favicon');
        Cache::forget('exchange_coin_percentage');

        CacheHelper::cacheSettings();
    }
}
