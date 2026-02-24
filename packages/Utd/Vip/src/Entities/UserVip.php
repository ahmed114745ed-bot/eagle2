<?php

namespace Utd\Vip\Entities;

use App\Models\Admin;
use App\Models\Pack;
use App\Models\User;
use App\Traits\AutoReceiveType;
use App\Traits\TimestampsWithTimezone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVip extends Model
{
    use AutoReceiveType, SoftDeletes, TimestampsWithTimezone;

    protected $table = 'users_vips';

    protected $guarded = [];

    public function senderable(): MorphTo
    {
        return $this->morphTo(null, 'sender_type', 'sender_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function OVip()
    {
        return $this->belongsTo(OVip::class, 'vip_id', 'id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'vip_user_id', 'id');
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($userVip) {});
    }
}
