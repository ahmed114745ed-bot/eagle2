<?php

namespace Modules\Events\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PkWinner extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pkEvent()
    {
        return $this->belongsTo(PkEvent::class,'pk_event_id');
    }

    public function rewardsPk()
    {
        return $this->belongsToMany(PkReward::class,'pk_winner_id','pk_reward_id');
    }

    public function reward()
    {
        return $this->belongsTo(PkReward::class,'pk_reward_id');
    }

    public function winner()
    {
        return $this->hasOne(User::class,'pk_winner_id');
    }

}
