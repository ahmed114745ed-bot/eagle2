<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OverrideCountry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $countryId = $request->header('X-Country-ID') ?? $request->query('country_id');

        if ($countryId) {
            session(['admin_country_id' => $countryId]);
        } else {
            $countryId = session('admin_country_id');
        }

        if ($countryId) {
            if (Admin::user()) {
                $user = Admin::user();
                $user->setAttribute('country_id', $countryId);
            }
        }


        return $next($request);
    }
}
