<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Form\Field\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BlackList extends Model
{
    protected $table = 'black_lists';

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
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function blockedPerson()
    {
        return $this->belongsTo(User::class,'from_uid');
    }

    public function scopeBetweenUsers($query, $userId, $otherUserId){
        return $query->where("user_id", $userId)->where("from_uid", $otherUserId)
        ->orwhere("user_id", $otherUserId)->where("from_uid",$userId);
    }
}
