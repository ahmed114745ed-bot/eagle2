<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCountry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($countryId = $request->header('X-Country-ID')) {
            session(['country_id' => $countryId]);
        } elseif ($request->has('country_id')) {
            session(['country_id' => $request->get('country_id')]);
        } else {
            session()->forget('country_id');
        }

        return $next($request);
    }
}
