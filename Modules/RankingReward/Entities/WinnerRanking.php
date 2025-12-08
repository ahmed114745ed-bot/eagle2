<?php

namespace Modules\RankingReward\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerRanking extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'winner_id','reward_id'];
}
