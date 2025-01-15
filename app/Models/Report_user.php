<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report_user extends Model
{
    use HasFactory;
    protected $table = 'reports';
    protected $fillable = [
        'type',
        'report_details',
        'user_id',
        'Reporter_id',
        'image',


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
    public function scopeFilter($query, $uid)
    {
        if ($uid) {
        User::where('id', 'LIKE', "%{$uid}%");
        }
    }

    public function report()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Reporter()
    {
        return $this->belongsTo(User::class, 'Reporter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
