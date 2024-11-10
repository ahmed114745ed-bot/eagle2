<?php

namespace Modules\DailyPrize\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyGiftType extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
            DailyGift::where('type',$model->type)->delete();
        });
    }
}
