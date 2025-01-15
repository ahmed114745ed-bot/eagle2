<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ware extends Model
{
    protected $table = 'wares';
    protected $guarded=['id'];

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
        static::saving(function ($model) {
            if ($model->status) {
                unset($model->status);
            }
        });
        // Listen for the 'deleting' event of the Agency model
        static::deleting(function ($id) {

            $pack = Pack::where('target_id', $id->id)->delete();
        });
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'target_id');
    }

    public function scopeIsNotUsedInPacks(Builder $query)
    {
        return $query->whereDoesntHave("packs", function ($q) {
            $q->where(fn($q)=>$q->where('packs.expire', 0)->orWhere('packs.expire', '>=', time()));
        });
    }

    public function scopeShowUserCustom(Builder $query, int $userId)
    {
        return $query->where(fn($q) =>
            $q->whereDoesntHave('ware_users')
            ->orWhereHas('ware_users', fn($q) => $q->where('user_id', $userId))
        );
    }


    public function ware_users()
    {
        return $this->belongsToMany(User::class, 'user_ware','ware_id','user_id')->withPivot('disable');
    }


}
