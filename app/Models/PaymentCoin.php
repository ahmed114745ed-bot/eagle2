<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCoin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function coins()
    {
        return $this->hasMany(Coin::class, 'payment_gateway_id');
    }
}
