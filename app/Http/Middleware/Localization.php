<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {


        $langCode = $request->header('X-localization', 'en');

        $language = Language::where('code', $langCode)->where('is_enabled', true)->first();

        if ($language) {
            app()->setLocale($language->code);
        }

   

        // // Check header request and determine localizaton
        // $local = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : 'en';
        // // set laravel localization
        // app()->setLocale($local);
        // // continue request
        return $next($request);
    }
}
