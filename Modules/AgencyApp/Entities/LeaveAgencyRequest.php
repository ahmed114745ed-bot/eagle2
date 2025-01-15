<?php

namespace Modules\AgencyApp\Entities;

use App\Facades\UserHandling;
use App\Models\Agency;
use App\Models\Agent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveAgencyRequest extends Model
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
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Agent::class,"admin_id");
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if($model->status == 1)
            {
                $user = User::find($model->user_id);
                UserHandling::kickUserFromAgency($user);
            }

        });
    }
}
