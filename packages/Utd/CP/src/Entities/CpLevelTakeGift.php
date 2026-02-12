<?php

namespace Utd\CP\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class CpLevelTakeGift extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = [];
}
