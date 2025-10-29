<?php

namespace App\Models;

use App\Traits\TimestampsWithTimezone;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubAreaManager extends Model
{
    use TimestampsWithTimezone;

    protected $table = 'admin_users';

    protected $attributes = [
        'type' => 'area-manager',
    ];

    public function appUser()
    {
        return $this->belongsTo(User::class, 'app_id');
    }

    public function agencies()
    {
        return $this->hasMany(Agency::class, 'country_id', 'country_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    protected static function booted(): void
    {

        self::addGlobalScope('subAreaManagerOnly', function (Builder $builder) {
            $builder->where('type', 'sub_area_manager');
        });

        self::deleting(function (SuperAdmin $superAdmin) {
        });
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

        });

        self::updating(function ($model) {

        });
    }


}
