<?php

namespace App\Traits;

use App\Models\Bd;

trait DefaultBdAssignmentTrait
{
    public static function bootDefaultBdAssignmentTrait()
    {
        static::creating(function ($model) {
            if (empty($model->bd_id)) {
                $defaultBd = Bd::where('type', 'bd')
                                ->where('default', true)
                                ->first();

                if ($defaultBd) {
                    $model->bd_id = $defaultBd->app_id;
                }
            }
        });

        static::updating(function ($model) {
            if (empty($model->bd_id)) {
                $defaultBd = Bd::where('type', 'bd')
                                ->where('default', true)
                                ->first();

                if ($defaultBd) {
                    $model->bd_id = $defaultBd->app_id;
                }
            }
        });
    }
}
