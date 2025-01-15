<?php

namespace Modules\Charizma\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExtraDataInRoom extends Model
{
    use HasFactory;

    protected $table = 'extra_data_in_rooms';

    protected $fillable = ['user_id','total','room_id'];

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


}
