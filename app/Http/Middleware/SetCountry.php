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
    // public function handle(Request $request, Closure $next): Response
    // {
    //     $countryId = $request->get('filter_country_id');

    //     if ($request->has('filter_country_id')) {
    //         if ($countryId === null || $countryId === '' || $countryId === 'null') {
    //             session()->forget('filter_country_id');
    //         } else {
    //             session(['filter_country_id' => $countryId]);
    //         }
    //     }

    //     return $next($request);
    // }


      public function handle(Request $request, Closure $next): Response
    {
        $countryId = $request->get('filter_country_id');
        $shouldClearCountry = $request->get('clear_country');

        $areaManagerCountryId = $request->get('area_manager_country_id');
        $shouldClearAreaManagerCountry = $request->get('clear_area_manager_country');

        $areaManagerId = $request->get('area_manager_id');
        $shouldClear = $request->get('clear_area_manager');

        if ($shouldClearCountry == 1) {
            session()->forget('filter_country_id');
        } elseif ($countryId !== null && $countryId !== '' && $countryId !== 'null') {
            session(['filter_country_id' => $countryId]);
        }

        if ($shouldClearAreaManagerCountry == 1) {
            session()->forget('area_manager_country_id');
        } elseif ($areaManagerCountryId !== null && $areaManagerCountryId !== '' && $areaManagerCountryId !== 'null') {
            session(['area_manager_country_id' => $areaManagerCountryId]);
        }

        if ($shouldClear == 1) {
            session()->forget('area_manager_id');
        } elseif ($areaManagerId !== null && $areaManagerId !== '' && $areaManagerId !== 'null') {
            session(['area_manager_id' => $areaManagerId]);
        }

        return $next($request);
    }
}
