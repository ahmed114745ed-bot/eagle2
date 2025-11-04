<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       
            if (Auth::check()) {
                $user = Auth::user();
                $cacheKey = 'user_last_seen_' . $user->id;
    
                if (!Cache::has($cacheKey)) {
                    $user->update([
                        'last_seen_at' => now(),
                        'online' => true,
                    ]);
    
                    Cache::put($cacheKey, true, now()->addMinutes(2));
                }
            }
    
            return $next($request);
        
    }
}
