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
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
        return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $timeZone = request()->header('tz') ?? 'UTC';
        //$timeZone = 'Asia/Dhaka'; // Get the user's time zone from the session
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
