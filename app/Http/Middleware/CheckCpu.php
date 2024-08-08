<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckCpu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $url = $request->url();
        if (config('app.env') != 'local' && Str::contains($url, 'lucky-gift')) {
            $loadAverage = sys_getloadavg();
            $cpuUsage    = $loadAverage[0];
            if ($cpuUsage > 11.5) {
                // Take appropriate action, e.g., stop the request
                return response(__('api.try_again'), 503);
            }
        }

        return $next($request);
    }
}
