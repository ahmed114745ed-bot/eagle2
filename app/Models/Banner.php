<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Banner extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['publish'];
    protected $casts = [
        'is_active' => 'boolean',
    ];

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
    public function scopeWhereIsNotSeen(Builder $query, $utcTimestamp): Builder
    {
        return $query->whereDate('publish_at', '>', Carbon::createFromTimestamp($utcTimestamp, 'utc'));
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (request('publish') == 'on') {
                $model->publish_at = now();
            } else {
                $model->publish_at = null;
            }
            unset($model->publish);
        });

        static::updating(function ($model) {
            if (request('publish') == 'on') {

                $model->publish_at = now();
            } else {
                $model->publish_at = null;
            }
            unset($model->publish);
        });

        // static::deleting(function ($banner) {

        //     if (auth()->user() && $banner->creator?->isRole('developer')) {
        //         abort(403);
        //     }
        // });


    }

    public function creator(){
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function getPublishAttribute()
    {
        return $this->publish_at == null ? 0 : 1;
    }


}
