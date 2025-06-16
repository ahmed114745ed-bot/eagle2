<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coin extends Model
{
    use SoftDeletes, TimestampsWithTimezone;

    protected $guarded = ['id'];

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
