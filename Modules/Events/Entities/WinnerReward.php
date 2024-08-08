<?php

namespace Modules\Events\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerReward extends Model{
    use HasFactory;

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function winner()
    {
        return $this->belongsTo(User::class);
    }
}
