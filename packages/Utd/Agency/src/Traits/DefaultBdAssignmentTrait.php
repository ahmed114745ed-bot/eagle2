<?php

namespace Utd\Agency\Traits;

trait DefaultBdAssignmentTrait
{
    public static function bootDefaultBdAssignmentTrait()
    {
        static::creating(function ($model) {
            if (empty($model->bd_id)) {
                $bdClass = config('agency-package.models.bd', \Utd\Bd\Entities\Bd::class);

                $defaultBd = $bdClass::where('type', 'bd')
                    ->where('default', true)
                    ->where('country_id', $model->country_id)
                    ->first();

                if ($defaultBd) {
                    $model->bd_id = $defaultBd->id;
                }
            }
        });

        static::updating(function ($model) {
            if (empty($model->bd_id)) {
                $bdClass = config('agency-package.models.bd', \Utd\Bd\Entities\Bd::class);

                $defaultBd = $bdClass::where('type', 'bd')
                    ->where('default', true)
                    ->where('country_id', $model->country_id)
                    ->first();

                if ($defaultBd) {
                    $model->bd_id = $defaultBd->id;
                }
            }
        });
    }
}
