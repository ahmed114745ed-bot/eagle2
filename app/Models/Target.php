<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{

    protected $fillable = [
        'id',
        'level',
        'diamonds',
        'minuts',
        'days',
        'hours',
        'usd',
        'agency_share',
        'moment',
        'reel',
        'gold',
        'coin',
        'img',
        'app_profit_percentage',
        'db_percentage'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
           
            if (isset($model->usd) && isset($model->agency_share) &&  isset($model->db_percentage)) {
                $model->app_profit_percentage = 100 - (double) $model->usd - (double) $model->agency_share - (double) $model->db_percentage;
            }


        });
    }

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

    // protected static function boot()
    // {
    //     parent::boot();

    //     // static::deleting(function ($banner) {

    //     //     if (auth()->user() && $banner->creator?->isRole('developer')) {
    //     //         abort(403);
    //     //     }
    //     // });
    // }

    public function creator(){
        return $this->belongsTo(Admin::class, 'created_by');
    }

    //     public function setReelAttribute($values)
    // {
    //     $this->attributes['reel'] = implode(',', $values);
    // }
    // public function setMomentAttribute($values)
    // {
    //     $this->attributes['moment'] = implode(',', $values);
    // }
}
