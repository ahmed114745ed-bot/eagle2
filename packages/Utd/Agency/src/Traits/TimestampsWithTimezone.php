<?php

namespace Utd\Agency\Traits;

use DateTimeZone;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait TimestampsWithTimezone
{
    public function getCreatedAtAttribute($value)
    {
        $tz = $this->getTimezone();

        return Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        $tz = $this->getTimezone();

        return Carbon::parse($value)->setTimezone($tz)->format('Y-m-d H:i:s');
    }

    protected function getTimezone()
    {
        // Try to get from request header first
        $tz = request()->header('tz');
        if ($tz && $this->isValidTimezone($tz)) {
            return $tz;
        }

        // Fallback to cache/settings or default
        return Cache::rememberForever('timezone', function () {
            // Avoid direct dependency on App\Models\Setting
            $setting = DB::table('settings')->where('key', 'timezone')->first();

            return $setting?->value ?? 'UTC';
        });
    }

    protected function isValidTimezone($timezoneId)
    {
        if (! $timezoneId) {
            return false;
        }
        try {
            new DateTimeZone($timezoneId);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
