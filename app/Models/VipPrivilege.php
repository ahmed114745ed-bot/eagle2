<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class VipPrivilege extends Model
{
    protected $table = 'vip_privileges';

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
    public function getItem($vip){
        $i = Ware::query ()->where ('get_type',1)->where ('type',$this->type)->where('level',$vip)->first ();
        return $i;
    }

    public function vip(){
        return $this->belongsToMany (VipPrivilege::class,'vip_prev','o_vip_privilege_id', 'o_vip_id','id','id');
    }
}
