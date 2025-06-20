<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class LiveTime extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'live_times';

    protected $guarded = ['id'];
}
