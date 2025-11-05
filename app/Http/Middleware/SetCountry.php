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
        $countryId = $request->get('filter_country_id');

        if ($request->has('filter_country_id')) {
            if ($countryId === null || $countryId === '' || $countryId === 'null') {
                session()->forget('filter_country_id');
            } else {
                session(['filter_country_id' => $countryId]);
            }
        }

        return $next($request);
    }
}
