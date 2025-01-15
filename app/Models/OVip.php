<?php

namespace App\Models;

use App\Selectables\Privileges;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OVip extends Model
{
    protected $table = 'o_vips';
    protected $fillable = [
        'name',
        'level',
        'price',
        'exp',
        'expire',

        'img'
    ];

    protected $hidden = ['privileges'];

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
    public function privilegs()
    {
        return $this->belongsToMany(VipPrivilege::class, 'vip_prev', 'o_vip_id', 'o_vip_privilege_id', 'id', 'id');
    }

    public function wareIcon()
    {
        return $this->hasOne(Ware::class, 'level', 'level')
            ->where('type', 12)
            ->where('get_type', 1);
    }
}
