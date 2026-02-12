<?php

namespace Utd\Room;

use App\Contracts\EnteranceRoomContract;
use App\Contracts\RoomGameContract;
use App\Contracts\RoomRepositoryContract;
use App\Contracts\RoomSalaryRepositoryContract;
use App\Contracts\RoomServiceContract;
use App\Contracts\RoomTopUsersRepositoryContract;
use App\Contracts\RoomVisitorRepositoryContract;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Room\Entities\Room;
use Utd\Room\Observers\RoomObserver;
use Utd\Room\Repositories\RoomRepoInterface;
use Utd\Room\Repositories\RoomRepository;
use Utd\Room\Repositories\RoomSalaryRepository;
use Utd\Room\Repositories\RoomTopUsersRepository;
use Utd\Room\Repositories\RoomVisitorRepository;
use Utd\Room\Services\EntranceRoomService;
use Utd\Room\Services\RoomGameServices;
use Utd\Room\Services\RoomService;

class RoomServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Room\\Http\\Controllers';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/room.php', 'room');

        // Bind the repository interface to the implementation (RoomRepository now implements both)
        $this->app->bind(RoomRepoInterface::class, RoomRepository::class);

        // Bind EnteranceRoomContract so external code can use it
        $this->app->singleton(EnteranceRoomContract::class, EntranceRoomService::class);

        // Bind RoomGameContract so external code can use it
        $this->app->singleton(RoomGameContract::class, RoomGameServices::class);

        // Bind RoomServiceContract so external code can use it
        $this->app->singleton(RoomServiceContract::class, RoomService::class);

        // Bind RoomRepositoryContract so external code can use it
        $this->app->singleton(RoomRepositoryContract::class, RoomRepository::class);

        // Bind RoomSalaryRepositoryContract so external code can use it
        $this->app->singleton(RoomSalaryRepositoryContract::class, RoomSalaryRepository::class);

        // Bind RoomTopUsersRepositoryContract so external code can use it
        $this->app->singleton(RoomTopUsersRepositoryContract::class, RoomTopUsersRepository::class);

        // Bind RoomVisitorRepositoryContract so external code can use it
        $this->app->singleton(RoomVisitorRepositoryContract::class, RoomVisitorRepository::class);
    }

    /**
     * Boot the application events.
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerObservers();
    }

    /**
     * Register the package commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\RemoveBackgroundCron::class,
                Console\Commands\UpdateRoomBanCommand::class,
            ]);
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->group(__DIR__.'/../Routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__.'/../Routes/web.php');

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->namespace)
            ->group(__DIR__.'/../Routes/utd.php');

        Route::prefix('api/dashboard')
            ->middleware(['api'])
            ->group(__DIR__.'/../Routes/dashboard.php');
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'room');
    }

    /**
     * Register the package translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'room');
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__.'/../Config/room.php' => config_path('room.php'),
            ], 'room-config');
            //
            //            // Views
            //            $this->publishes([
            //                __DIR__ . '/../Resources/views' => resource_path('views/vendor/room'),
            //            ], 'room-views');
            //
            //            // Translations
            //            $this->publishes([
            //                __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/room'),
            //            ], 'room-lang');
            //
            //            // Migrations
            //            $this->publishes([
            //                __DIR__ . '/../Database/migrations' => database_path('migrations'),
            //            ], 'room-migrations');
        }
    }

    protected function registerObservers(): void
    {
        Room::observe(RoomObserver::class);
    }
}
