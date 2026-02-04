<?php

namespace Modules\UsersWallet\Entities;

use App\Models\User;
use App\Models\Admin;
use App\Models\Target;
use App\Models\ShippingAgency;
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
        'related_id',
        'auth_id'
    ];

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class);
    }
    public function target()
    {
        return $this->belongsTo(Target::class, 'related_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'related_id');
    }

    public function authAdmin()
    {
        return $this->belongsTo(Admin::class, 'auth_id');
    }



    public function related()
    {
        return $this->belongsTo(User::class, 'related_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shippingAgency()
    {
        return $this->belongsTo(ShippingAgency::class, 'related_id');
    }
}
