<?php

namespace App\Models;

use App\Helpers\UserCoinLogHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class UserVip extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'users_vips';

    protected $guarded = ['id'];

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
    protected static function booted()
    {
        static::created(function ($userVip) {
            if ($userVip->user_id && ($userVip->price ?? 0) > 0) {
                UserCoinLogHelper::log(
                    $userVip->user_id,
                    'vip',
                    'users_vips',
                    $userVip->price
                );
            }
        });
    }
}
