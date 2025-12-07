<?php

namespace Modules\RankingReward\Entities;

use Illuminate\Database\Eloquent\Model;

class RankingReward extends Model
{
    protected $fillable = ['ranking_range_id', 'target_type', 'target', 'expire_days'];
}
