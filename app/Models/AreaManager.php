<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class AreaManager extends Authenticatable
{
    use HasFactory;

    protected $table = 'admin_users';

    protected $attributes = [
        'type' => 'area-manager',
    ];

 
    protected static function booted(): void
    {
        self::addGlobalScope('AreaManagerOnly', function (Builder $builder) {
            $builder->where('type', 'area-manager');
        });
    }

    public function appUser()
    {
        return $this->belongsTo(User::class, 'app_id');
    }

    public function polygon()
    {
        return $this->hasOne(AreaPolygon::class, 'area_manager_id');
    }
}
