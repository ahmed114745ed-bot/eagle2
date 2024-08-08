<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_payment_gateways', 'user_id', 'payment_gateway_id')->withTimestamps();
    }
}
