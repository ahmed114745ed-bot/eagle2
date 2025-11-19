<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RoomCupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $roomCup = \Cache::rememberForever('room_cup', function () {
            $setting =   Setting::where('key', 'room_cup')->first();
            return $setting?->value ?? 0;
        });
        if (!$roomCup) return response()->json(['error' => 'something wrong'], 500);

        return $next($request);
    }
}
