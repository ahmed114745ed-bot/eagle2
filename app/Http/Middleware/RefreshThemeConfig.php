<?php

namespace App\Http\Middleware;

use App\Helpers\CacheHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refresh theme config on every request.
 *
 * On Swoole Octane the AppServiceProvider::boot() runs only once per worker,
 * so Config::set('themes.*') values become stale after settings are updated.
 * This middleware re-reads the cached settings (which are invalidated on save)
 * and pushes fresh values into the runtime config before the view is rendered.
 */
class RefreshThemeConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $settings = CacheHelper::cacheSettings();

            if (gettype($settings) !== 'array') {
                $settings = $settings->pluck('value', 'key')->toArray();
            }

            Config::set([
                'themes.primaryColor'          => $settings['primary_color'] ?? '#FF9428',
                'themes.secondaryColor'        => $settings['secondary_color'] ?? '#1A1A1A',
                'themes.textPrimaryColor'      => $settings['text_primary_color'] ?? '#fdf8f8',
                'themes.textSecondaryColor'    => $settings['text_secondary_color'] ?? '#c1b9b9',
                'themes.boxBackgroundColor'    => $settings['box_background_color'] ?? '#222222',
                'themes.backgroundImage'       => $settings['app_background'] ?? '',
                'themes.brandBackgroundImage'  => $settings['brand_background_image'] ?? '',
                'themes.tableBackGroundColor'  => $settings['table_background_color'] ?? '#c88213',
            ]);
        } catch (\Throwable $e) {
            // Silently fail – don't break the request if cache is unavailable
        }

        return $next($request);
    }
}
