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
        $this->refreshCache();
    }

    public function deleted(Setting $setting): void
    {
        $this->refreshCache();
    }

    protected function refreshCache(): void
    {
        Cache::forget('all_settings');

        // Also clear individual color/theme cache keys so rememberForever picks up new values
        $themeKeys = [
            'primary_color', 'secondary_color', 'text_primary_color',
            'text_secondary_color', 'box_background_color', 'app_background',
            'brand_background_image', 'table_background_color', 'dark_mode',
        ];

        foreach ($themeKeys as $key) {
            Cache::forget($key);
        }

        CacheHelper::cacheSettings();
    }
}
