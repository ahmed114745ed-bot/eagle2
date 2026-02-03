<?php

namespace Utd\Gifts;

use Illuminate\Support\ServiceProvider;

class GiftsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/gifts.php', 'gifts'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'gifts');

        // Publish config
        $this->publishes([
            __DIR__.'/../Config/gifts.php' => config_path('gifts.php'),
        ], 'gifts-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../Database/migrations' => database_path('migrations'),
        ], 'gifts-migrations');

        // Publish views
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/gifts'),
        ], 'gifts-views');
    }
}
