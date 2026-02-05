<?php

namespace Utd\Gifts;

use App\Contracts\GiftsContract;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Utd\Gifts\Services\GiftsService;
use Utd\Gifts\Contracts\GiftSenderInterface;
use Utd\Gifts\Services\GiftSenderService;
use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Listeners\IncrementReceiverDiamond;
use Utd\Gifts\Listeners\SendGiftNotification;
use Utd\Gifts\Listeners\UpdateAgencySalary;
use Utd\Gifts\Listeners\UpdateUserLevels;
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
        // Bind Repository Contracts
        $this->app->singleton(\App\Contracts\GiftLogRepositoryContract::class, \Utd\Gifts\Repositories\GiftLogRepository::class);
        $this->app->singleton(\App\Contracts\GiftRepositoryContract::class, \Utd\Gifts\Repositories\GiftRepository::class);

        // Bind GiftSenderInterface (NEW - Main service)
        $this->app->singleton(GiftSenderInterface::class, GiftSenderService::class);

        // Bind old GiftsContract for backward compatibility
        $this->app->bind(GiftsContract::class, function($app) {
            return new GiftsService();
        });

        // Bind old services for backward compatibility
        $this->app->bind(\Utd\Gifts\Services\GiftService::class, function($app) {
            return new \Utd\Gifts\Services\GiftService(
                $app->make(\Utd\Gifts\Repositories\GiftRepository::class)
            );
        });

        $this->app->bind(\Utd\Gifts\Services\GiftLogService::class, function($app) {
            return $app->make(\Utd\Gifts\Services\GiftLogService::class);
        });

        if (!class_exists('App\Tik\Services\GiftService', false)) {
            $this->app->bind('App\Tik\Services\GiftService', \Utd\Gifts\Services\GiftService::class);
        }
        
        if (!class_exists('App\Tik\Services\GiftLogService', false)) {
            $this->app->bind('App\Tik\Services\GiftLogService', \Utd\Gifts\Services\GiftLogService::class);
        }

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

        // Register event listeners (NEW)
        $this->registerEventListeners();
    }

    /**
     * Register event listeners
     */
    protected function registerEventListeners(): void
    {
        Event::listen(GiftSent::class, [
            IncrementReceiverDiamond::class,
            SendGiftNotification::class,
            UpdateAgencySalary::class,
            UpdateUserLevels::class,
        ]);
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
