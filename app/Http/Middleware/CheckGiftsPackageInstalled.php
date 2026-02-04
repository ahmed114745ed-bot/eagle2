<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckGiftsPackageInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if Gifts package is installed
        if (!class_exists(\Utd\Gifts\GiftsServiceProvider::class)) {
            // For admin routes - return 404
            if ($request->is('admin/*')) {
                abort(404, 'Gifts Package is not installed');
            }
            
            // For API routes - return JSON error
            return response()->json([
                'error' => 'Gifts Package is not installed',
                'message' => 'This feature requires the Gifts package to be installed.'
            ], 404);
        }

        return $next($request);
    }
}
