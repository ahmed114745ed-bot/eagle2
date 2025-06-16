<?php

namespace Modules\Public\Entities;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class WinnerLevelInterval extends Model
{
    use TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function levelInterval()
    {
        return $this->belongsTo(LevelInterval::class, 'level_interval_id');
    }
}
