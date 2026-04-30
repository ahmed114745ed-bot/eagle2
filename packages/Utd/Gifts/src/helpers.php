<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('getGiftPercentage')) {
    function getGiftPercentage(string $key): float
    {
        $cacheKey = "percentage_{$key}";

        $value = Cache::get($cacheKey);

        if ($value === null) {
            $value = \App\Models\Setting::where('key', $key)->value('value');
            if ($value !== null) {
                Cache::put($cacheKey, $value);
            }
        }
        if ($value === null) {
            $value = match ($key) {
                'app_wallet_lucky_gift' => 80,
                'owner_lucky_gift'      => 10,
                'host_lucky_gift'       => 10,
                default                  => 0,
            };
        }

        $percentage = round(((float) $value) / 10, 2);

        return $percentage;
    }
}
