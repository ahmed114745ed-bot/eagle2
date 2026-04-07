<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use KevinSoft\MultiLanguage\MultiLanguage;

class EnhancedMultiLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $cookieName = MultiLanguage::config('cookie-name', 'locale');
        $languages = MultiLanguage::config('languages', []);
        
        // Priority order for locale detection:
        // 1. URL parameter (for new tabs/windows)
        // 2. Custom header (for AJAX requests)
        // 3. Existing cookie
        // 4. Session
        // 5. Default application locale
        
        $locale = null;
        
        // Check URL parameter first (highest priority for new tabs)
        if ($request->has('locale') && array_key_exists($request->input('locale'), $languages)) {
            $locale = $request->input('locale');
            
            // Set cookie for future requests
            cookie()->queue($cookieName, $locale, 525600); // 1 year
        }
        // Check custom header (for AJAX requests)
        elseif ($request->hasHeader('X-Locale') && array_key_exists($request->header('X-Locale'), $languages)) {
            $locale = $request->header('X-Locale');
            
            // Set cookie for future requests
            cookie()->queue($cookieName, $locale, 525600); // 1 year
        }
        // Check existing cookie
        elseif ($request->cookie($cookieName) && array_key_exists($request->cookie($cookieName), $languages)) {
            $locale = $request->cookie($cookieName);
        }
        // Check session
        elseif ($request->session()->has($cookieName) && array_key_exists($request->session()->get($cookieName), $languages)) {
            $locale = $request->session()->get($cookieName);
            
            // Move to cookie for better persistence
            cookie()->queue($cookieName, $locale, 525600); // 1 year
        }
        
        // Set locale if found
        if ($locale) {
            app()->setLocale($locale);
            
            // Store in session for backup
            session([$cookieName => $locale]);
        }
        
        return $next($request);
    }
}
