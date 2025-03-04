<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomView extends Model
{
    protected $table = 'rooms_view_with_today_rank';
    protected $appends = ['lang','country'];


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
    public function getRoomBackgroundAttribute($val){
        return @Background::query ()->where ('id',$val)->first ()->img;
    }

    public function owner(){
        return $this->belongsTo (User::class,'uid','id');
    }

    public function getLangAttribute(){
        return @$this->owner->country->language;
    }

    public function getCountryAttribute(){
        $country = @$this->owner->country;
        return $country;

    }


    public function myClass(){
        return $this->belongsTo (RoomCategory::class,'room_class')->select ('name','img');
    }

    public function myType(){
        return $this->belongsTo (RoomCategory::class,'room_type')->select ('name','img');
    }
}
