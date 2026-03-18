<?php

namespace Modules\Reals\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReportReals extends Model
{
    protected $fillable = [];
    protected $guarded = [];
    public function reel()
    {
        return $this->hasOne(Real::class, 'id', 'real_id');
    }
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
    // protected $table = ['Report_reals'];

      public function reporter()
        {
            return $this->belongsTo(User::class, 'Reporter_id');
        }

        public function reportedUser()
        {
            return $this->belongsTo(User::class, 'Reported_id');
        }

    
}
