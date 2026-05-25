<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\VersionHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckAppVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $platform = $request->header('X-Platform');
        $version = $request->header('X-App-Version');

        if (!$platform || !$version) {
            return $next($request);
        }

        $versionCheck = VersionHelper::checkVersion($platform, $version);

        if (!$versionCheck['valid']) {
            return response()->json([
                'status' => 0,
                'message' => $versionCheck['message'],
                'data' => [
                    'update_required' => true,
                    'min_version' => $versionCheck['min_version'] ?? null,
                ],
            ], 426);
        }

        if ($versionCheck['update_required']) {
            $request->attributes->set('update_available', true);
            $request->attributes->set('latest_version', $versionCheck['current_version'] ?? null);
        }

        return $next($request);
    }
}
