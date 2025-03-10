<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vip extends Model
{
    use HasFactory;
        protected $fillable = [
            'type',
            'img',
            'exp',
            'level',
            'di',
            'co',
            'name_en',
            'name_ar',
            'created_by',
            'updated_by'
        ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($vip) {
            if (auth()->user() && $vip->creator?->isRole('developer')) {
                abort(403);
            }
        });
    }

    public function creator(){

        return $this->belongsTo(Admin::class, 'created_by');

    }
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
    /*public function gifts()
    {
        return $this->hasMany(GiftRoomLevel::class,'level_id');
    }*/
}
