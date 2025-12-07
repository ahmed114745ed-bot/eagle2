<?php

namespace Modules\RankingReward\Entities;

use Illuminate\Database\Eloquent\Model;

class RankingType extends Model
{
    protected $fillable = ['type', 'schedule'];
}
