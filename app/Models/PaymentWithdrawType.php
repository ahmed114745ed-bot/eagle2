<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWithdrawType extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function withdrawFields()
    {
        return $this->hasMany(PaymentWithdrawField::class,'payment_withdraw_type_id');
    }

    public function userWithdrawFields()
    {
        return $this->hasMany(UserPaymentWithdrawField::class,'payment_withdraw_type_id');
    }
}
