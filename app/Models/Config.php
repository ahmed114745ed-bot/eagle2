<?php

namespace App\Models;

use Artisan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $guarded = ['id'];

    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->valueSelect) {
                unset($model->valueSelect);
            }
            if ($model->valueInteger) {
                unset($model->valueInteger);
            }
            $keys = ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster'];
            if (in_array($model->name, $keys)) {
                Cache::forget('pusher_config');
                Artisan::call('config:cache');
            }
        });
    }
}
