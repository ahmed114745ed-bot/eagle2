<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\CP\Entities\CpLevel;
use Modules\CP\Entities\CpRelation;

class Cp extends Model
{
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
    public function fromUser()
    {
        return $this->belongsTo(User::class,"user_one_id");
    }

    public function level(){
        return $this->belongsTo(CpLevel::class, 'level_id');
    }
    public function toUser()
    {
        return $this->belongsTo(User::class,"user_two_id");
    }

    public function relation()
    {
        return $this->belongsTo(CpRelation::class,"cp_relation_id");
    }

    public function scopeRelation($query)
    {
        return $query->whereHas('relation', function ($query) {
            $query->where('title', 'lover');
        });
    }
}
