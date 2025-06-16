<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class CoinLog extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'coin_logs';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->with('profile');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }
}
