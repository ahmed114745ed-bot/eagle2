<?php

namespace Utd\Gifts;

use App\Contracts\GiftsContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Utd\Gifts\Console\GiftUpdateUsedCountMonthly;
use Utd\Gifts\Console\GiftUpdateUsedCountWeakly;
use Utd\Gifts\Contracts\GiftSenderInterface;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftCategory;
use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Listeners\IncrementReceiverDiamond;
use Utd\Gifts\Listeners\SendGiftNotification;
use Utd\Gifts\Listeners\UpdateAgencySalary;
use Utd\Gifts\Services\GiftSenderService;
use Utd\Gifts\Services\GiftsService;

//use Utd\Gifts\Listeners\UpdateUserLevels;

/**
 * GiftsServiceProvider
 *
 * Service Provider لنظام الهدايا
 */
class GiftsServiceProvider extends ServiceProvider
{
    protected $commands = [
        GiftUpdateUsedCountWeakly::class,
        GiftUpdateUsedCountMonthly::class,
    ];

    /**
     * Register services
     */
    public function register(): void
    {
        // Bind Repository Contracts
        $this->app->singleton(\App\Contracts\GiftLogRepositoryContract::class, Repositories\GiftLogRepository::class);
        $this->app->singleton(\App\Contracts\GiftRepositoryContract::class, Repositories\GiftRepository::class);

        // Bind package repositories to themselves for direct resolution
        $this->app->singleton(Repositories\GiftRepository::class);
        $this->app->singleton(Repositories\GiftLogRepository::class);

        // Bind GiftSenderInterface (NEW - Main service)
        $this->app->singleton(GiftSenderInterface::class, GiftSenderService::class);

        // Bind old GiftsContract for backward compatibility
        $this->app->bind(GiftsContract::class, function ($app) {
            return new GiftsService();
        });

        // Bind old services for backward compatibility
        $this->app->singleton(Services\GiftService::class, function ($app) {
            return new Services\GiftService(
                $app->make(Repositories\GiftRepository::class)
            );
        });

        $this->app->singleton(Services\GiftLogService::class, function ($app) {
            return new Services\GiftLogService(
                $app->make(Repositories\GiftRepository::class),
                $app->make(Repositories\GiftLogRepository::class)
            );
        });

        if (! class_exists('App\Tik\Services\GiftService', false)) {
            $this->app->bind('App\Tik\Services\GiftService', Services\GiftService::class);
        }

        if (! class_exists('App\Tik\Services\GiftLogService', false)) {
            $this->app->bind('App\Tik\Services\GiftLogService', Services\GiftLogService::class);
        }

        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/gifts.php',
            'gifts'
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        if (file_exists(__DIR__.'/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        // Load admin routes
        if (file_exists(__DIR__.'/../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        }

        // Publish config
        $this->publishes([
            __DIR__.'/../config/gifts.php' => config_path('gifts.php'),
        ], 'gifts-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
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
     * Get the services provided by the provider
     */
    public function provides()
    {
        return [GiftsContract::class];
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
//            UpdateUserLevels::class,
        ]);
    }

    /**
     * Register console commands
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register model observers
     */
    protected function registerObservers()
    {
        if (class_exists('Utd\Gifts\Observers\GiftObserver')) {
            Gift::observe(Observers\GiftObserver::class);
        }

        if (class_exists('Utd\Gifts\Observers\GiftCategoryObserver')) {
            GiftCategory::observe(Observers\GiftCategoryObserver::class);
        }
    }
}
