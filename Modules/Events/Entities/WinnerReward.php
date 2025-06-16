<?php

namespace Modules\Events\Entities;

use App\Models\User;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerReward extends Model
{
    use HasFactory, TimestampsWithTimezone;

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function winner()
    {
        return $this->belongsTo(User::class);
    }
}
