<?php

namespace App\Services;

use App\Models\AppFeature;
use Nette\Schema\ValidationException;

class AppFeatureService
{

    public static function isEnable($slug)
    {
        $appFeature = AppFeature::where("slug",$slug)->first();
        if ($appFeature != null && $appFeature->status == 0) {
            return false;
        }
        return true;
    }
    //UserTargetAchieveJob
    public function validateStatusEnable($slug)
    {
        $appFeature = AppFeature::where("slug",$slug)->first();
        if ($appFeature != null && $appFeature->status == 0) {
            abort(403, __('This feature has not been activated for you'));
        }
        return true;
    }
}
