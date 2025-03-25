<?php

namespace Modules\Moment\Entities;

use App\Models\User;
use App\Models\Setting;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReportMoment extends Model
{
    protected $fillable = [];
    protected $guarded = [];

    // protected $table = ['Report_reals'];

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
    public function moment()
    {
        return $this->belongsTo(Moment::class);
    }
    public function reporter()
    {
        return $this->belongsTo(User::class, 'Reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'Reported_id');
    }
}

