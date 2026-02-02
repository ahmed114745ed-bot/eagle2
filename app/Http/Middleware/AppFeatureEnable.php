<?php

namespace App\Http\Middleware;

use App\Helpers\Common;
use App\Helpers\AgencyPackageHelper;
use App\Models\AppFeature;
use App\Services\AppFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppFeatureEnable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$slug): Response
    {
        // Check if agencies feature and package is not installed
    
        /*   if (isset($slug[0]) && $slug[0] === 'agencies' && !AgencyPackageHelper::isAgencyInstalled()) {
            if ($request->is('api/*')) {
                return Common::apiResponse(0, __('api_responses.feature_not_avilable'), []);
            } else {
                abort(403, __('This feature has not been activated for you'));
            }
        }*/

      /*  $appFeature = AppFeature::where("slug",$slug[0])->first();
        if ($appFeature != null && $appFeature->status == 0) {
            if ($request->is('api/*')) {
                // Handle API response
                return Common::apiResponse(0, __('api_responses.feature_not_avilable'), []);
            } else {
                abort(403, __('This feature has not been activated for you'));
            }
        }*/

        return $next($request);
    }
}
