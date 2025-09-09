<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;

class CoinLog extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'coin_logs';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->with('profile')->where('user_type','user');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }


    public function shippingAgency()
    {
        return $this->belongsTo(ShippingAgency::class, 'user_id')->where('user_type','shipping_agency');
    }
}
