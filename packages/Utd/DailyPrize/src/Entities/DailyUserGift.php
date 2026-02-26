<?php

namespace Utd\DailyPrize\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyUserGift extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];
}
