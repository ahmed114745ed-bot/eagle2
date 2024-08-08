<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaymentWithdrawField extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function payment_withdraw_field(){
        return $this->belongsTo(PaymentWithdrawField::class,'payment_withdraw_field_id');
    }
}
