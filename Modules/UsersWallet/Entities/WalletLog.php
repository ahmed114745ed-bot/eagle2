<?php
namespace Modules\UsersWallet\Entities;

use App\Models\Target;
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
        return $this->belongsTo(UserWallet::class);
    }
        public function target()
    {
        return $this->belongsTo(Target::class, 'target_id');
    }
}
