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
