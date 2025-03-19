<?php

namespace App\Models;

use App\Helpers\Common;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    public static $withoutAppends = false;

    protected $guarded = ['id'];

    public function getAvatarAttribute()
    {
        if (self::$withoutAppends) {
            return;
        }
        return @$this->profile()->first()->avatar ?: Common::getConf('default_img');
    }
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
