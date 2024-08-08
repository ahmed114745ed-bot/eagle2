<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentWithdraw extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function payment_withdraw_type(){
        return $this->belongsTo(PaymentWithdrawType::class)->with("userWithdrawFields");
    }
}
