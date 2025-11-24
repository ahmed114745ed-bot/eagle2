<?php

namespace App\Services;

use App\Models\AppFeature;
use Illuminate\Support\Facades\Cache;
use Nette\Schema\ValidationException;

class AppFeatureService
{

    public static function isEnable($slug)
    {
        return Cache::rememberForever("app_feature_status_{$slug}", function () use ($slug) {
            $feature = AppFeature::where('slug', $slug)->first();
            if ($feature && $feature->status == 0) {
                return false;
            }
            return true;
        });
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
