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
    ];
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($banner) {

            if (auth()->user() && $banner->creator?->isRole('developer')) {
                abort(403);
            }
        });
    }

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
