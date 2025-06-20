<?php

namespace Modules\Events\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralRole extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];
}
