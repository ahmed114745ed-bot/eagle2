<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomZegoMessage extends Model
{
    use HasFactory;
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
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class,'room_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class,'gift_id');
    }


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $user = User::find($model->user_id);
            $model->room_id = $user->ownerRoom->id;
        });

        static::saving(function ($model) {
            if ($model->isDirty('user_id')) {
                $user = User::find($model->user_id);
                $model->room_id = $user->ownerRoom->id;
            }
        });
    }
}
