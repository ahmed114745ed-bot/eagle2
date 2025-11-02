<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AreaManager extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'admin_users';

    protected $attributes = [
        'type' => 'area-manager',
    ];

    protected $dates = ['deleted_at'];


    protected static function booted(): void
    {
        self::addGlobalScope('AreaManagerOnly', function (Builder $builder) {
            $builder->where('type', 'area-manager');
        });


        static::deleting(function ($manager) {
            $defaultManager = self::where('default', 1)->first();

            if (!$defaultManager) {
                throw new \Exception('❌ لا يمكن الحذف — لا يوجد مدير افتراضي محدد.');
            }
            if ($manager->id !== $defaultManager->id) {
                \App\Models\Country::where('area_manager_id', $manager->id)
                    ->update(['area_manager_id' => $defaultManager->id]);
            }
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

    public function agencies()
    {
        return $this->hasManyThrough(Agency::class, Country::class, 'area_manager_id', 'country_id', 'id', 'id');
    }

    public function countries()
    {
        return $this->hasMany(Country::class, 'area_manager_id');
    }
}
