<?php

namespace Modules\RankingReward\Entities;

use Illuminate\Database\Eloquent\Model;

class RankingRange extends Model
{
    protected $fillable = ['ranking_type_id', 'min', 'max'];
}
