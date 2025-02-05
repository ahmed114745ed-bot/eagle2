<?php

namespace Modules\CP\Entities;

use App\Models\Vip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CpLevel extends Model
{
    protected $guarded = [];

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

/*     public function gifts(){
        return $this->hasManyThrough(
            CpLevelGift::class, // Final model (Gifts)
            Vip::class,         // Intermediate model (Vips)
            'id',               // Local key on Vips (relates to cp_level_gifts.vip_id)
            'vip_id',           // Foreign key on cp_level_gifts
            'id',               // Local key on cp_levels
            'id'                // Local key on Vips
        );
    } */

    public function gifts(){
        return $this->hasMany(CpLevelGift::class, 'vip_id', 'id');
    }
}
