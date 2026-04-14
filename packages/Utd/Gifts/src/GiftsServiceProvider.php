<?php

namespace Utd\Gifts;

use App\Contracts\GiftLogRepositoryContract;
use App\Contracts\GiftRepositoryContract;
use App\Contracts\GiftsContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Utd\Gifts\Console\GiftUpdateUsedCountMonthly;
use Utd\Gifts\Console\GiftUpdateUsedCountWeakly;
use Utd\Gifts\Entities\Gift;
use Utd\Gifts\Entities\GiftCategory;
use Utd\Gifts\Events\GiftSent;
use Utd\Gifts\Listeners\IncrementReceiverDiamond;
use Utd\Gifts\Listeners\SendGiftNotification;
use Utd\Gifts\Listeners\UpdateAgencySalary;
use Utd\Gifts\Observers\GiftCategoryObserver;
use Utd\Gifts\Observers\GiftObserver;
use Utd\Gifts\Services\GiftSenderService;
use Utd\Gifts\Services\GiftsService;

// use Utd\Gifts\Listeners\UpdateUserLevels;

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
        $this->app->singleton(GiftLogRepositoryContract::class, Repositories\GiftLogRepository::class);
        $this->app->singleton(GiftRepositoryContract::class, Repositories\GiftRepository::class);

        // Bind package repositories
        $this->app->singleton(Repositories\GiftRepository::class);
        $this->app->singleton(Repositories\GiftLogRepository::class);

        // Bind GiftSenderService
        $this->app->singleton(GiftSenderService::class);

        // Bind GiftsContract
        $this->app->bind(GiftsContract::class, function ($app) {
            return new GiftsService();
        });

        // Bind services
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

        // Register event listeners
        $this->registerEventListeners();
    }

    public function provides()
    {
        return [GiftsContract::class];
    }

    protected function registerEventListeners(): void
    {
        Event::listen(GiftSent::class, [
            IncrementReceiverDiamond::class,
            SendGiftNotification::class,
            UpdateAgencySalary::class,
            //            UpdateUserLevels::class,
        ]);
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    protected function registerObservers()
    {
        Gift::observe(GiftObserver::class);
        GiftCategory::observe(GiftCategoryObserver::class);
    }
}
