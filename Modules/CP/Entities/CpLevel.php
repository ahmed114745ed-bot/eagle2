<?php

namespace Modules\CP\Entities;

use App\Models\Setting;
use App\Models\Vip;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CpLevel extends Model
{
    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
            // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = Cache::rememberForever($cacheKey, function () {
        $setting = Setting::where('key', 'timezone')->first();
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
            // Cache key for the timezone setting
    $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = Cache::rememberForever($cacheKey, function () {
        $setting = Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

/*     public function gifts(){
        return $this->hasManyThrough(
            CpLevelGift::class, // Final model (Gifts)
            Vip::class,         // Intermediate model (Vips)
            'id',               // Local key on Vips (relates to cp_level_gifts.vip_id)
            'vip_id',           // Foreign key on cp_level_gifts
            'id',               // Local key on cp_levels
            'id'                // Local key on Vips
        );
    } */

    public function gifts(){
        return $this->hasMany(CpLevelGift::class, 'vip_id', 'id');
    }
}
