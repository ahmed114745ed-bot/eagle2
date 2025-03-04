<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RoomCategory extends Model
{
    protected $table = 'room_categories';
    protected $guarded = [];
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
    public function typeRooms(){
        return $this->hasMany (Room::class,'room_type')->select ('id','numid','room_name','room_cover','room_intro');
    }

    public function classRooms(){
        return $this->hasMany (Room::class,'room_class')->select ('id','numid','room_name','room_cover','room_intro');
    }

    public function children(){
        return $this->hasMany (RoomCategory::class,'parent_id');
    }

    public function parent(){
        return $this->belongsTo (RoomCategory::class,'parent_id');
    }
}
