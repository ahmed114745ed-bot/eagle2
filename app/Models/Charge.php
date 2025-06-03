<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Charge extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $fillable = ['id', 'charger_id', 'charger_type', 'user_id', 'user_type', 'amount', 'amount_type', 'balance_before', 'agency_id', 'is_used_transferred','usd','user_charger_type'];

    public function getCreatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }

    // Convert updated_at to the user's local time zone
    public function getUpdatedAtAttribute($value)
    {
        $cacheKey = 'timezone';

    // Retrieve the timezone setting from cache, or fetch it from the database if not cached
    $timezone = \Cache::rememberForever($cacheKey, function () {
        $setting = \App\Models\Setting::where('key', 'timezone')->first();
        return $setting?->value ?? 'UTC';
    });

    // Get the timezone from the request header or use the cached setting
    $timeZone = request()->header('tz') ?? $timezone;

    // Parse the date and set the timezone
    return Carbon::parse($value)->setTimezone($timeZone)->format('Y-m-d H:i:s');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender()
    {
        if ($this->charger_type == 'agency') {
            return $this->hasOne(ShippingAgency::class, 'id', 'charger_id');
        }
        return $this->hasOne(User::class, 'id', 'charger_id');
    }

    public function senderAll()
    {
       
            return $this->belongsTo(
                $this->charger_type === 'agency'
                    ? ShippingAgency::class
                    : User::class,
                'charger_id',
                'id'
            );
        
    }

    public function receiver()
    { 
        if ($this->user_type == 'agency') {
            return $this->belongsTo(ShippingAgency::class, 'agency_id', 'id');
        }
        return $this->belongsTo(User::class, 'user_id', 'id');
    
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'charger_id');
    }

    public function admin_user()
    {
        return $this->belongsTo(AdminUser::class, 'charger_id');
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
    public function shippingAgency()
    {
        return $this->belongsTo(ShippingAgency ::class, 'agency_id');
    }
    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });

        static::deleted(function ($model) {
            if ($model->agency_id) {
                clearAgencyCache($model->agency_id);
            }
        });
    }

}
