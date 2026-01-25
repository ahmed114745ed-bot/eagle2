<?php

namespace Utd\Reals\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class VideoCategory extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = [];
}
