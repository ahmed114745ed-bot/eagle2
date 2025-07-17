<?php

namespace App\Models;

use App\Models\Scopes\HostAgencyScope;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = ['id', 'charger_id', 'charger_type', 'user_id', 'user_type', 'amount', 'amount_type', 'balance_before', 'agency_id', 'is_used_transferred', 'usd', 'user_charger_type', 'action_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        if ($this->charger_type === 'agency') {
            return $this->hasOne(ShippingAgency::class, 'id', 'charger_id');
        }

        return $this->hasOne(User::class, 'id', 'charger_id');
    }

    public function getSenderAllAttribute()
    {
        if ($this->charger_type === 'agency') {
            return ShippingAgency::find($this->charger_id);
        }

        return User::find($this->charger_id);
    }

    public function getReceiverAllAttribute()
    {
        if ($this->user_type === 'agency') {
            return ShippingAgency::find($this->charger_id);
        }

        return User::find($this->charger_id);
    }

    public function reason()
    {
        return $this->hasMany(ChargeInvoice::class, 'charge_id');
    }

    public function receiver()
    {
        if ($this->user_type === 'agency') {
            return $this->belongsTo(ShippingAgency::class, 'agency_id', 'id');
        }

        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // public function admin()
    // {
    //     return $this->belongsTo(Admin::class, 'charger_id');
    // }

    public function admin_user()
    {
        return $this->belongsTo(AdminUser::class, 'charger_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id')
            ->withoutGlobalScope(HostAgencyScope::class);
    }

    public function shippingAgency()
    {
        return $this->belongsTo(ShippingAgency::class, 'agency_id');
    }

    public function receiverage()
    {
        return $this->belongsTo(Agency::class, 'agency_id')
            ->withoutGlobalScope(HostAgencyScope::class);
    }

    /**
     * receiver ############################
     */
    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiveragency()
    {
        return $this->belongsTo(Agency::class, 'user_id')
            ->withoutGlobalScope(HostAgencyScope::class);
    }

    /**
     * sender ############################
     */
    public function senderUser()
    {
        return $this->belongsTo(User::class, 'charger_id');
    }

    public function senderAgency()
    {
        return $this->belongsTo(Agency::class, 'charger_id');
    }

    public function senderShippingAgency()
    {
        return $this->belongsTo(ShippingAgency::class, 'charger_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'charger_id');
    }

    protected static function booted()
    {
        self::saved(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });

        self::deleted(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });
    }
}
