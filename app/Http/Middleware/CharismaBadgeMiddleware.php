<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CharismaBadgeMiddleware
{
    /**
     * Handle an incoming request.
     * Blocks the request if the 'charisma_badge' setting is disabled.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $charismaBadge = Cache::rememberForever('charisma_badge', function () {
            return Setting::where('key', 'charisma_badge')->value('value') ?? 0;
        });

        if (!$charismaBadge) {
            return response()->json([
                'status' => 0,
                'message' => 'Charisma badge feature is currently disabled.',
                'data' => [],
            ], 403);
        }

        return $next($request);
    }
}
