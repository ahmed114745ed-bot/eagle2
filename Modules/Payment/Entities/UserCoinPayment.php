<?php

namespace Modules\Payment\Entities;

use App\Models\Coin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCoinPayment extends Model
{
    use HasFactory;

    protected $table = 'user_coin_payments';
    protected $guarded = [];
    protected $fillable = [];

    protected static function newFactory()
    {
//        return \Modules\Payment\Database\factories\UserCoinPaymentFactory::new();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
    }

    public function coin()
    {
        return $this->hasOne(Coin::class, 'id','coin_id');
    }
}
