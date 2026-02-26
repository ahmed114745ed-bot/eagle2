<?php

namespace Utd\DailyPrize\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyGiftCount extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $fillable = [];

    protected $guarded = [];
}
