<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HomeCarousel extends Model
{
    protected $table = 'home_carousels';
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
    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function room()
    {
        return $this->hasOne(Room::class, 'uid', 'owner_id');
    }




    protected static function boot()
    {
        parent::boot();


        static::creating(function ($model) {
            if ($model->form != null) {
                $newDuration = Carbon::now();
                $duration = match ($model->form) {
                    '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                    '2' => $newDuration->addDays($model->input),
                    '3' => $newDuration->addMonths($model->input),
                    default => null
                };
                $model->duration  = $duration->timestamp;
            }
        });

        static::saving(function ($model) {
            $newDuration = Carbon::now();
            if ($model->form != null) {
                if ($model->isDirty('input') || $model->isDirty('form')) {
                    $duration = match ($model->form) {
                        '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                        '2' => $newDuration->addDays($model->input),
                        '3' => $newDuration->addMonths($model->input),
                        default => null
                    };
                    $model->duration  = $duration->timestamp;
                }
            }
        });


        static::updating(function ($model) {
            $newDuration = Carbon::now();
            if ($model->form != null) {
                if ($model->isDirty('input') || $model->isDirty('form')) {
                    $duration = match ($model->form) {
                        '1' => $model->input > 1 ? $newDuration->addHours($model->input) : $newDuration->addMinute($model->input * 60),
                        '2' => $newDuration->addDays($model->input),
                        '3' => $newDuration->addMonths($model->input),
                        default => null
                    };
                    $model->duration  = $duration->timestamp;
                }
            }
        });
    }
}
