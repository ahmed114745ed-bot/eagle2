<?php

namespace App\Models;

use App\Facades\CustomNotification;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class OfficialMessageAdmin extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'official_messages';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            CustomNotification::officialMsg($model);
        });
    }
}
