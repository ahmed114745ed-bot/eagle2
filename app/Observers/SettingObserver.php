<?php

namespace App\Observers;

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

        Cache::rememberForever('all_settings', function () {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
}
