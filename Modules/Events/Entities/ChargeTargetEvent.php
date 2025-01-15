<?php

namespace Modules\Events\Entities;

use App\Models\Ware;
use Carbon\Carbon;
use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Events\Database\factories\TargetEventFactory;

class ChargeTargetEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];
    protected $table   = 'charge_events';
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
    public function rewards()
    {
        return $this->hasMany(RewardTarget::class, 'charge_event_id')->with("ware",'vip');
    }

    public function ware()
    {
        return $this->rewards->ware();
    }

    public function getWareAttribute()
    {
        $wares = $this->rewards->map(function ($reward) {
            return $reward->ware;
        })->filter();

        return $wares;
    }

}
