<?php

namespace App\Http\Middleware;

use App\Support\PackageHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPackageInstalled
{
    public function handle(Request $request, Closure $next, string ...$packages): Response
    {
        foreach ($packages as $package) {
            if (!PackageHelper::isInstalled($package)) {
                return response()->json([
                    'status'  => false,
                    'message' => "Package [{$package}] is not installed.",
                    'code'    => 'PACKAGE_NOT_INSTALLED',
                ], 403);
            }
        }

        return $next($request);
    }
}
