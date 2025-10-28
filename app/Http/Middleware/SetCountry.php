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
        $countryId = $request->get('country_id');
        $areaManagerCountryId = $request->get('area_manager_country_id');
        $areaManagerId = $request->get('area_manager_id');

        if ($request->has('country_id')) {
            if ($countryId === null || $countryId === '' || $countryId === 'null') {
                session()->forget('country_id');
            } else {
                session(['country_id' => $countryId]);
            }
        }

        if ($request->has('area_manager_country_id')) {
            if ($areaManagerCountryId === null || $areaManagerCountryId === '' || $areaManagerCountryId === 'null') {
                session()->forget('area_manager_country_id');
            } else {
                session(['area_manager_country_id' => $areaManagerCountryId]);
            }
        }

        if ($request->has('area_manager_id')) {
            if ($areaManagerId === null || $areaManagerId === '' || $areaManagerId === 'null') {
                session()->forget('area_manager_id');
            } else {
                session(['area_manager_id' => $areaManagerId]);
            }
        }

        return $next($request);
    }
}
