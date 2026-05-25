<?php

namespace App\Observers;

use App\Helpers\CacheHelper;
use App\Models\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConfigObserver
{
    public function saved(Config $config): void
    {
        $this->refreshCache();
    }

    public function deleted(Config $config): void
    {
        $this->refreshCache();
    }

    protected function refreshCache(): void
    {
        Cache::forget('all_configs');

        foreach (['en', 'ar'] as $code) {
            Cache::forget("badges_{$code}");
        }

        CacheHelper::cacheConfig();
    }
}
