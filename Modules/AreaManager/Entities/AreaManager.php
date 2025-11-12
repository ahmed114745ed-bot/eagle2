<?php

namespace Modules\AreaManager\Entities;

use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use App\Models\AreaPolygon;
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

            if ($manager->default == 1) {
                throw new Exception(__('can not delete default area admin'));
            }
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

    public function flag()
    {

        $countries = Country::where('area_manager_id', $this->id)->get();

        $html = '<div class="user-type-badges">';
        foreach ($countries as $country) {
            $url = getImagePath($country->flag);

            if ($url) {
                $html .= handleShowImageWithTypes($country->id, $url, 20, 10, 4);
            // '<img src="' . e( $url) . '" alt="' . e($country->name) . '" style="width: 100px; height: 100px; object-fit: contain; border-radius: 4px; margin-right: 4px;">';

            }
        }

        $html .= '</div>';


        return $html;
    }
}
