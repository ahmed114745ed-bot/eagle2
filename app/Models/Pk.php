<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pk extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

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
    public function getT1PerAttribute()
    {
        if (($this->t1_score + $this->t2_score) > 0) {
            $res = $this->t1_score / ($this->t1_score + $this->t2_score);
        } else {
            $res = 0.5;
        }
        return number_format($res, 2);
    }

    public function getT2PerAttribute()
    {
        if (($this->t1_score + $this->t2_score) > 0) {
            $res = $this->t2_score / ($this->t1_score + $this->t2_score);
        } else {
            $res = 0.5;
        }
        return number_format($res, 2);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
