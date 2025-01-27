<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Modules\CP\Entities\CpInfo;

class GiftLog extends Model
{
    protected $table = 'gift_logs';

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
    public function gift()
    {
        return $this->belongsTo(Gift::class, 'giftId', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function roomOwner()
    {
        return $this->belongsTo(User::class, 'roomowner_id');
    }

    public function cps()
    {
        return $this->belongsTo(Cp::class, 'cp_id');
    }

    public function cp()
    {
        return $this->belongsTo(Cp::class, 'cp_id')->with("fromUser:id,uuid,name", "toUser:id,uuid,name", 'level');
    }
}
