<?php

namespace Modules\Public\Entities;

use Illuminate\Database\Eloquent\Model;

class LevelInterval extends Model
{
    protected $guarded = ['id'];

    public function rewards()
    {
        return $this->hasMany(RewardLevelInterval::class,'level_interval_id');
    }
}
