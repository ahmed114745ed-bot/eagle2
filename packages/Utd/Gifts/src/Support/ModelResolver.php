<?php

namespace Utd\Gifts\Support;

/**
 * ModelResolver
 * 
 * Helper class لحل الـ Models الخارجية بشكل ديناميكي
 * يجعل الباكيج مستقل ولا يتأثر بالتغييرات الخارجية
 */
class ModelResolver
{
    /**
     * الحصول على User Model class
     * 
     * @return string|null
     */
    public static function getUserModel(): ?string
    {
        $model = config('gifts.models.user', 'App\Models\User');
        return class_exists($model) ? $model : null;
    }

    /**
     * الحصول على VIP Model class
     * 
     * @return string|null
     */
    public static function getVipModel(): ?string
    {
        $model = config('gifts.models.vip', 'Modules\Vip\Entities\OVip');
        return class_exists($model) ? $model : null;
    }

    /**
     * الحصول على Moment Model class
     * 
     * @return string|null
     */
    public static function getMomentModel(): ?string
    {
        $model = config('gifts.models.moment', 'Utd\Moments\Entities\Moment');
        return class_exists($model) ? $model : null;
    }

    /**
     * الحصول على Room Model class
     * 
     * @return string|null
     */
    public static function getRoomModel(): ?string
    {
        $model = config('gifts.models.room', 'Utd\Room\Entities\Room');
        return class_exists($model) ? $model : null;
    }

    /**
     * الحصول على Agency Model class
     * يستخدم Helper خارجي إذا كان موجود
     * 
     * @return string|null
     */
    public static function getAgencyModel(): ?string
    {
        // محاولة استخدام الـ Helper الخارجي
        $helperClass = config('gifts.helpers.agency_helper');
        
        if ($helperClass && class_exists($helperClass) && method_exists($helperClass, 'getAgencyClass')) {
            $agencyClass = $helperClass::getAgencyClass();
            if ($agencyClass && class_exists($agencyClass)) {
                return $agencyClass;
            }
        }

        // fallback للـ config
        $model = config('gifts.models.agency');
        return ($model && class_exists($model)) ? $model : null;
    }

    /**
     * الحصول على Null Agency Model (fallback)
     * 
     * @return string
     */
    public static function getNullAgencyModel(): string
    {
        return config('gifts.models.null_agency', 'App\Models\NullAgency');
    }

    /**
     * التحقق من وجود Trait
     * 
     * @param string $traitKey
     * @return bool
     */
    public static function hasTrait(string $traitKey): bool
    {
        $trait = config("gifts.traits.{$traitKey}");
        return $trait && trait_exists($trait);
    }

    /**
     * الحصول على Trait
     * 
     * @param string $traitKey
     * @return string|null
     */
    public static function getTrait(string $traitKey): ?string
    {
        $trait = config("gifts.traits.{$traitKey}");
        return trait_exists($trait) ? $trait : null;
    }

    /**
     * الحصول على Package Helper
     * 
     * @return string|null
     */
    public static function getPackageHelper(): ?string
    {
        $helper = config('gifts.helpers.package_helper', 'App\Support\PackageHelper');
        return class_exists($helper) ? $helper : null;
    }

    /**
     * التحقق من وجود relationship ديناميكياً
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $relationName
     * @param string $relationType
     * @return mixed|null
     */
    public static function checkRelation($model, string $relationName, string $relationType)
    {
        $packageHelper = self::getPackageHelper();
        
        if ($packageHelper && method_exists($packageHelper, 'checkRelation')) {
            return $packageHelper::checkRelation($model, $relationName, $relationType);
        }
        
        return null;
    }

    /**
     * إنشاء relationship فارغ (للـ fallback)
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function emptyRelation($model)
    {
        return $model->belongsTo(get_class($model), 'id', 'id')->whereRaw('1 = 0');
    }
}
