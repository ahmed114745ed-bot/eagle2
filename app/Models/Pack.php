<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pack extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    protected $dates = ['deleted_at'];

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
    public function ware()
    {
        return $this->belongsTo(Ware::class,'target_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function sender()
    {
        return $this->belongTo(User::class,'sender_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('expire', 0)->orWhere('expire', '>=', now()->timestamp);
        })->where('is_used', 1);
    }

}
