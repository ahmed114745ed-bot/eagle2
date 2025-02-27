<?php

namespace Modules\Moment\Entities;

use Carbon\Carbon;
use App\Models\Gift;
use App\Models\User;
use App\Models\MomentGallery;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
    protected $fillable = ['user_id','description','img'];
    protected $table = 'moment';
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

    public function comments()
    {
        return $this->hasMany( MomentCommint::class, 'moment_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany( MomentLikes::class, 'moment_id', 'id');
    }
    public function gifts()
    {
        return $this->belongsToMany(Gift::class, 'moment_user_gifts');
    }

    public function images()
    {
        return $this->hasMany(MomentGallery::class);
    }



    public function user()
    {
        return $this->belongsTo(User::class,);
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'moment_user_gifts')->withPivot('num', 'created_at','updated_at');
    // }

    public function scopeLikeExists($query, $userId)
    {
        return $query->withExists(['likes' => function($query) use($userId){
            $query->where('user_id', $userId);
        } ]);
    }



}
