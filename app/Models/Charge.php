<?php

namespace App\Models;

use App\Models\Scopes\HostAgencyScope;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Modules\AreaManager\Entities\AreaManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AreaManager\Entities\SubAreaManager;
use Modules\SuperAdmin\Entities\SubAdmin;
use Modules\SuperAdmin\Entities\SuperAdmin;

class Charge extends Model
{
    use HasFactory, TimestampsWithTimezone;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = ['id', 'charger_id', 'charger_type', 'user_id', 'user_type', 'amount', 'amount_type', 'balance_before', 'agency_id', 'is_used_transferred', 'usd', 'user_charger_type', 'action_user_id', 'total_coins', 'transaction_type', 'rate_source', 'applied_coin_rate', 'base_usd', 'base_coins', 'bonus_coins', 'profit_usd', 'profit_coins'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function returnCharge()
    {
        return $this->hasOne(ReturnCharge::class, 'charge_id');
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

    public function receiverSubAdmin()
    {
        return $this->belongsTo(SubAdmin::class, 'user_id');
    }

    public function receiverSubAreaManager(): BelongsTo
    {
        return $this->belongsTo(SubAreaManager::class, 'user_id');
    }

    public function receiverSuperAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'user_id');
    }

    public function receiverSubSuperAdmin(): BelongsTo
    {
        return $this->belongsTo(SubAdmin::class, 'user_id');
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

    public function areaManager(): BelongsTo
    {
        return $this->belongsTo(AreaManager::class, 'charger_id');
    }

    public function subAreaManager(): BelongsTo
    {
        return $this->belongsTo(SubAreaManager::class, 'charger_id');
    }

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class, 'charger_id');
    }

    public function subSuperAdmin(): BelongsTo
    {
        return $this->belongsTo(SubAdmin::class, 'charger_id');
    }

    public function bd()
    {
        return $this->belongsTo(Bd::class, 'charger_id');
    }


    /**
     * Snapshot fields that should be immutable after creation.
     * These fields are critical for financial audit and reporting accuracy.
     */
    protected static array $immutableSnapshotFields = [
        'applied_coin_rate',
        'base_usd',
        'base_coins',
        'bonus_coins',
        'profit_usd',
        'profit_coins',
        'total_coins',
        'rate_source',
    ];

    protected static function booted()
    {
        // Protect snapshot fields from modification after creation
        static::updating(function (Charge $charge) {
            foreach (self::$immutableSnapshotFields as $field) {
                // Only protect if the original value was set (not null)
                if ($charge->isDirty($field) && $charge->getOriginal($field) !== null) {
                    throw new \Exception(
                        __("Snapshot field ':field' cannot be modified after creation. Original: :original, New: :new", [
                            'field' => $field,
                            'original' => $charge->getOriginal($field),
                            'new' => $charge->getAttribute($field),
                        ])
                    );
                }
            }
        });

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

    /**
     * Check if a field is an immutable snapshot field.
     *
     * @param string $field
     * @return bool
     */
    public static function isImmutableField(string $field): bool
    {
        return in_array($field, self::$immutableSnapshotFields);
    }

    /**
     * Get all immutable snapshot field names.
     *
     * @return array
     */
    public static function getImmutableFields(): array
    {
        return self::$immutableSnapshotFields;
    }
}
