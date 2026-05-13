<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\PackageHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckPackageInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $package  اسم الحزمة للتحقق منها (مثل: bd, agency, gifts)
     */
    public function handle(Request $request, Closure $next, string $package): Response
    {
        // التحقق من تثبيت الحزمة
        if (!PackageHelper::isInstalled($package)) {
            // إذا كان الطلب AJAX أو API
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Package not installed',
                    'message' => "حزمة {$package} غير مثبتة. يرجى تثبيتها أولاً.",
                    'package' => $package,
                ], 503);
            }

            // للطلبات العادية (Web)
            return response()->view('errors.package-not-installed', [
                'package' => $package,
                'message' => "حزمة {$package} غير مثبتة حالياً"
            ], 503);
        }

        return $next($request);
    }
}
