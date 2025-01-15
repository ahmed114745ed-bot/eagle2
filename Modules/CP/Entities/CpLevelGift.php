<?php

namespace Modules\CP\Entities;

use App\Models\OVip;
use App\Models\Vip;
use App\Models\Ware;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CpLevelGift extends Model
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
    public function cp_level()
    {
        return $this->belongsTo(CpLevel::class,'vip_id');
    }

    public function vip()
    {
        return $this->belongsTo(OVip::class,'item_id');
    }
    public function ware()
    {
        return $this->belongsTo(Ware::class,'item_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if ($model->coins) {
                unset($model->coins);
            }
            if ($model->achievement) {
                unset($model->achievement);
            }
        });

    }
}
