<?php

namespace App\Models;

use DB;
use Exception;
use App\Traits\TimestampsWithTimezone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubAreaManager extends Model
{
    use TimestampsWithTimezone, SoftDeletes;

    protected $table = 'admin_users';

    protected $attributes = [
        'type' => 'area-manager',
    ];

    protected $dates = ['deleted_at'];

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

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class, 'area_manager_id', 'parent_id');
    }

    protected static function booted(): void
    {

        self::addGlobalScope('subAreaManagerOnly', function (Builder $builder) {
            $builder->where('type', 'sub_area_manager');
        });

        self::deleting(function (SuperAdmin $superAdmin) {});
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {});

        self::updating(function ($model) {});
    }
}
