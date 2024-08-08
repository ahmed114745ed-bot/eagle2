<?php

namespace Modules\Public\Entities;

use Illuminate\Database\Eloquent\Model;

class WinnerLevelInterval extends Model
{
    protected $guarded = ['id'];

    public function levelInterval()
    {
           return $this->belongsTo(LevelInterval::class,'level_interval_id');
    }
}
