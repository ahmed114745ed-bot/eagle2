<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GiftsMenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if gifts package is installed
        $giftsInstalled = class_exists(\Utd\Gifts\GiftsServiceProvider::class);

        // If not installed, hide menu items
        if (!$giftsInstalled && Schema::hasTable('admin_menu')) {
            $this->hideGiftsMenu();
        }

        // Add to view composer for menu
        View::composer('admin::partials.menu', function ($view) use ($giftsInstalled) {
            $view->with('gifts_installed', $giftsInstalled);
        });
    }

    /**
     * Hide gifts menu items from admin panel
     */
    protected function hideGiftsMenu(): void
    {
        try {
            // Gift-related menu IDs (from the database query result)
            $giftMenuIds = [20, 61, 130, 131, 158, 184, 241, 244, 259];

            // Hide them
            DB::table('admin_menu')
                ->whereIn('id', $giftMenuIds)
                ->update(['show' => 0]);

        } catch (\Exception $e) {
            // Silent fail - table might not exist yet
        }
    }
}
