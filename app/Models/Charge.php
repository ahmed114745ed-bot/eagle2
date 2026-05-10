<?php

namespace App\Models;

use App\Models\Scopes\HostAgencyScope;
use App\Support\PackageHelper;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Utd\Bd\Entities\Bd;
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

    protected $fillable = ['id', 'charger_id', 'charger_type', 'user_id', 'user_type', 'amount', 'amount_type', 'balance_before', 'agency_id', 'is_used_transferred', 'usd', 'user_charger_type', 'action_user_id'];

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
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'agency_id')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'agency_id')
            ->withoutGlobalScope(HostAgencyScope::class);
    }

    public function shippingAgency()
    {
        return $this->belongsTo(ShippingAgency::class, 'agency_id');
    }

    public function receiverage()
    {
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'agency_id')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'agency_id')
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
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'user_id')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'user_id')
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
        $agencyClass = config('agency-package.models.agency', \App\Models\Agency::class);
        if (!class_exists($agencyClass)) {
            return $this->belongsTo(self::class, 'charger_id')->whereRaw('1 = 0');
        }
        return $this->belongsTo($agencyClass, 'charger_id');
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

    public function bd()
    {
        return PackageHelper::checkRelation($this, 'bd', 'belongsTo')
            ?? $this->belongsTo(Bd::class, 'charger_id');
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
