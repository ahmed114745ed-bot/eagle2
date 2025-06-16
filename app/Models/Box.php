<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'boxs';

    protected $guarded = ['id'];
}
