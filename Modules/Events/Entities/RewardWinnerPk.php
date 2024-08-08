<?php

namespace Modules\Events\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardWinnerPk extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function reward()
    {
        return $this->belongsTo(PkReward::class,'pk_reward_id');
    }

    public function winner()
    {
        return $this->hasOne(User::class,'id','pk_winner_id');
    }

}
