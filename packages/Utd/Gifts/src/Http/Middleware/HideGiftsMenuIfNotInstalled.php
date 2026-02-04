<?php

namespace Utd\Gifts\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HideGiftsMenuIfNotInstalled
{
    /**
     * Handle an incoming request.
     * Hide gifts menu items from admin panel if package is not installed
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only run on admin routes
        if (!$request->is('admin/*')) {
            return $next($request);
        }

        // Check if package is installed
        $packageInstalled = class_exists(\Utd\Gifts\GiftsServiceProvider::class);

        if (!$packageInstalled) {
            // Hide menu items from database
            $this->hideGiftsMenuItems();
        } else {
            // Show menu items if package is installed
            $this->showGiftsMenuItems();
        }

        return $next($request);
    }

    /**
     * Hide gifts menu items
     */
    protected function hideGiftsMenuItems(): void
    {
        try {
            // List of gift-related menu URIs
            $giftUris = [
                '/gifts',
                '/gift-categories',
                '/lucky-gift-settings',
                'gift-summary',
                'daily-gift-types',
                '/wares_dedicate',
                '/vips_dedicate',
                '/level-intervals',
                'achievement-dedicate'
            ];

            // Hide menus by setting show = 0
            DB::table('admin_menu')
                ->where(function ($query) use ($giftUris) {
                    foreach ($giftUris as $uri) {
                        $query->orWhere('uri', 'like', "%{$uri}%");
                    }
                })
                ->orWhere('title', 'like', '%gift%')
                ->orWhere('title', 'like', '%Gift%')
                ->update(['show' => 0]);

        } catch (\Exception $e) {
            // Silently fail if admin_menu table doesn't exist or has issues
            \Log::warning('Failed to hide gifts menu: ' . $e->getMessage());
        }
    }

    /**
     * Show gifts menu items
     */
    protected function showGiftsMenuItems(): void
    {
        try {
            // List of gift-related menu URIs
            $giftUris = [
                '/gifts',
                '/gift-categories',
                '/lucky-gift-settings',
                'gift-summary',
            ];

            // Show main gift menus
            DB::table('admin_menu')
                ->where(function ($query) use ($giftUris) {
                    foreach ($giftUris as $uri) {
                        $query->orWhere('uri', 'like', "%{$uri}%");
                    }
                })
                ->update(['show' => 1]);

        } catch (\Exception $e) {
            \Log::warning('Failed to show gifts menu: ' . $e->getMessage());
        }
    }
}
