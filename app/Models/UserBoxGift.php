<?php

namespace App\Models;

use App\Helpers\UserCoinLogHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class UserBoxGift extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'user_box_gifts';

    protected $guarded = ['id'];
    protected static function booted()
    {
        static::created(function ($gift) {
            if (($gift->user_id ?? null) && ($gift->coins ?? 0) > 0) {
                UserCoinLogHelper::log(
                    $gift->user_id,
                    'box_gift',
                    'user_box_gifts',
                    $gift->coins
                );
            }
        });
    }
}
