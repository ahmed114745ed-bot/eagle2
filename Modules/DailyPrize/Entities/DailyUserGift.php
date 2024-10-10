<?php

namespace Modules\DailyPrize\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DailyPrize\Database\factories\DailyUserGiftFactory;

class DailyUserGift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];


}
