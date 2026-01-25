<?php

namespace Modules\Reals\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class RealCategory extends Model
{
    use TimestampsWithTimezone;

    protected $fillable = [];

    protected $table = 'reals_categories';
}
