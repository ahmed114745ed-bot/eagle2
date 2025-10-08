<?php

namespace App\helper;

use Carbon\Carbon;
use DateTimeZone;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
class TimeHelper
{

    public static function weekStart()
    {
        return cache()->rememberForever('week_start', function () {
            return Setting::where('key', 'week_start')->value('value') ?? 'MONDAY';
        });
    }
    
    public static function weekEnd()
    {
        return cache()->rememberForever('week_end', function () {
            return Setting::where('key', 'week_end')->value('value') ?? 'SUNDAY';
        });
    }
    
    public static function startOfWeekConst()
    {
        return constant("Carbon\\Carbon::" . strtoupper(self::weekStart()));
    }
    
    public static function endOfWeekConst()
    {
        return constant("Carbon\\Carbon::" . strtoupper(self::weekEnd()));
    }

  
    public static function clearCache(): void
    {
        Cache::forget('timezone');
        Cache::forget('week_start');
        Cache::forget('week_end');
    }
 
}
