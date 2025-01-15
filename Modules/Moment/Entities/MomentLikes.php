<?php

namespace Modules\Moment\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MomentLikes extends Model
{
    protected $fillable = ['user_id','moment_id'];
    protected $table = 'moment_user_likes';
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
        return $this->hasOne(User::class, 'id','user_id');
    }
    public function moment()
    {
        return $this->hasOne(Moment::class, 'id','moment_id');
    }

    public function comments()
    {
        return $this->hasMany( MomentCommint::class, 'moment_id', 'moment_id');
    }

    public function likes()
    {
        return $this->hasMany( MomentLikes::class, 'moment_id', 'moment_id');
    }
    public function gifts()
    {
        return $this->hasMany( MomentLikes::class, 'moment_id', 'moment_id');
    }


}
