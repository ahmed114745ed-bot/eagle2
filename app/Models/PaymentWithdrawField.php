<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentWithdrawField extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function withdrawType()
    {
        return $this->belongsTo(PaymentWithdrawType::class,'payment_withdraw_type_id');
    }
}
