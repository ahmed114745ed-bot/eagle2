<?php

namespace Modules\Reals\Entities;

use App\Models\Interest;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Real extends Model
{
    protected $fillable = [];

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
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Real $real){
            if ($real->description == null){
                $real->description = '';
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Interest::class, RealCategory::class, 'real_id', 'category_id');
    }

    public function comments()
    {
        return $this->hasMany( RealUserComment::class, 'real_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany( RealUserLike::class, 'real_id', 'id');
    }
    public function Views()
    {
        return $this->hasMany( RealUserView::class, 'real_id', 'id');
    }


    public function user()
    {

        return $this->belongsTo(User::class,  'user_id');

    }
}
