<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\FixedTarget\Enums\TargetType;

class UserSallary extends Model
{
    protected $table = 'user_sallaries';

public bool $allowSaving = true;

    protected $guarded = ['id'];

    protected $casts = [
        'agency_sallary' => 'double',
        'sallary' => 'double',
        'extras' => 'json',
        'type' => TargetType::class,
        'year' => 'integer',
        'month' => 'integer',
    ];

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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
