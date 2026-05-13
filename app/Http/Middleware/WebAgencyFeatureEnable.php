<?php

namespace App\Http\Middleware;

use App\Helpers\AgencyPackageHelper;
use App\Support\PackageHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebAgencyFeatureEnable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$slug): Response
    {
        // التحقق من تثبيت الحزمة - إذا مش مثبتة، نسمح بالمرور
        if (!PackageHelper::isInstalled('agency')) {
            return $next($request);
        }

        // التحقق من الجداول - إذا مش موجودة، نسمح بالمرور
        if (!AgencyPackageHelper::isAgencyInstalled()) {
            return $next($request);
        }

        // التحقق من تفعيل الميزة
        $app_feature = \Cache::get('host_agency');

        if (!($app_feature == '1' || $app_feature == 1)) {
            abort(403, __('This feature has not been activated for you'));
        }

        return $next($request);
    }
}
