<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserVip extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'users_vips';

    protected $guarded = ['id'];

    public function senderable(): MorphTo
    {
        return $this->morphTo(null, 'sender_type', 'sender_id');
    }

    public function OVip()
    {
        return $this->belongsTo(OVip::class, 'vip_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'vip_user_id');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'dash_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->where('expire', '!=', 0)->where('expire', '>', Carbon::now()->timestamp)->orWhere('expire', 0);
        })->where('is_used', 1);
    }
    protected static function booted()
    {
        static::created(function ($userVip) {
            if ($userVip->user_id && ($userVip->price ?? 0) > 0) {
            }
        });
    }
}
