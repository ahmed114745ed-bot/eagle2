<?php
namespace Modules\UsersWallet\Entities;

use Illuminate\Database\Eloquent\Model;


class WalletLog extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'operation',
        'type',
        'before_amount',
        'after_amount',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
