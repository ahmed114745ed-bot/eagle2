<?php

namespace Modules\Events\Entities;

use App\Models\Setting;
use App\Models\Ware;
use Cache;
use Carbon\Carbon;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Events\Database\factories\TargetEventFactory;

class ChargeTargetEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table   = 'charge_events';
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
    public function rewards()
    {
        return $this->hasMany(RewardTarget::class, 'charge_event_id')->with("ware",'vip');
    }

    public function ware()
    {
        return $this->rewards->ware();
    }

    public function getWareAttribute()
    {
        $wares = $this->rewards->map(function ($reward) {
            return $reward->ware;
        })->filter();

        return $wares;
    }

}
