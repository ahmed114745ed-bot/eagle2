<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeEnterRoom extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];
}
