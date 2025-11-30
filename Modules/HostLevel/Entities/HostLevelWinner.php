<?php

namespace Modules\HostLevel\Entities;

use Carbon\Carbon;
use App\Traits\HostLevelTrait;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class HostLevelWinner extends Model
{
    use HasFactory, TimestampsWithTimezone, HostLevelTrait;
    protected $guarded = [];
}
