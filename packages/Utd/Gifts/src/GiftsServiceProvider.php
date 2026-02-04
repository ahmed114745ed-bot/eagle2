<?php

namespace Utd\Gifts;

use App\Contracts\GiftsContract;
use Illuminate\Support\ServiceProvider;
use Utd\Gifts\Services\GiftsService;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftCategory;

/**
 * GiftsServiceProvider
 * 
 * Service Provider لنظام الهدايا
 */
class GiftsServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Bind the GiftsContract to the real implementation
        $this->app->bind(GiftsContract::class, function($app) {
            return new GiftsService();
        });

        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/gifts.php',
            'gifts'
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
        
        // Load admin routes
        if (file_exists(__DIR__ . '/../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/gifts.php' => config_path('gifts.php'),
        ], 'gifts-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'gifts-migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        // Register observers
        $this->registerObservers();
    }

    /**
     * Register console commands
     */
    protected function registerCommands()
    {
        if (class_exists('Utd\Gifts\Console\Commands\GiftUpdateUsedCountWeakly')) {
            $this->commands([
                \Utd\Gifts\Console\Commands\GiftUpdateUsedCountWeakly::class,
            ]);
        }

        if (class_exists('Utd\Gifts\Console\Commands\GiftUpdateUsedCountMonthly')) {
            $this->commands([
                \Utd\Gifts\Console\Commands\GiftUpdateUsedCountMonthly::class,
            ]);
        }
    }

    /**
     * Register model observers
     */
    protected function registerObservers()
    {
        if (class_exists('Utd\Gifts\Observers\GiftObserver')) {
            Gift::observe(\Utd\Gifts\Observers\GiftObserver::class);
        }

        if (class_exists('Utd\Gifts\Observers\GiftCategoryObserver')) {
            GiftCategory::observe(\Utd\Gifts\Observers\GiftCategoryObserver::class);
        }
    }

    /**
     * Get the services provided by the provider
     */
    public function provides()
    {
        return [GiftsContract::class];
    }
}
