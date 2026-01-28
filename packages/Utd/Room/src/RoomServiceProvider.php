<?php

namespace Utd\Room;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Room\Services\RoomService;
use Utd\Room\Services\PkService;
use Utd\Room\Services\BanRoomService;
use Utd\Room\Services\BackgroundService;
use Utd\Room\Services\MicrophoneService;
use Utd\Room\Services\RoomSalaryService;
use Utd\Room\Services\RoomVisitorService;
use Utd\Room\Services\RoomCategoryService;
use Utd\Room\Repositories\PkRepository;
use Utd\Room\Repositories\RoomRepository;
use Utd\Room\Repositories\BanRoomRepository;
use Utd\Room\Repositories\BackgroundRepository;
use Utd\Room\Repositories\RoomSalaryRepository;
use Utd\Room\Repositories\RoomTargetRepository;
use Utd\Room\Repositories\RoomVisitorRepository;
use Utd\Room\Repositories\EnteredRoomRepository;
use Utd\Room\Repositories\RoomCategoryRepository;
use Utd\Room\Repositories\RoomMicrophoneRepository;
use Utd\Room\Repositories\RoomRepo;
use Utd\Room\Repositories\RoomRepoInterface;

class RoomServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Room\\Http\\Controllers';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/room.php', 'room');

        // Bind the repository interface to the implementation
        $this->app->bind(RoomRepoInterface::class, RoomRepo::class);
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the package commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Utd\Room\Console\Commands\RemoveBackgroundCron::class,
            ]);
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/utd.php');

        Route::prefix('api/dashboard')
            ->middleware(['api'])
            ->group(__DIR__ . '/../Routes/dashboard.php');
    }

    /**
     * Register the package views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'room');
    }

    /**
     * Register the package translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'room');
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../Config/room.php' => config_path('room.php'),
            ], 'room-config');

            // Views
            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/room'),
            ], 'room-views');

            // Translations
            $this->publishes([
                __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/room'),
            ], 'room-lang');

            // Migrations
            $this->publishes([
                __DIR__ . '/../Database/migrations' => database_path('migrations'),
            ], 'room-migrations');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            RoomService::class,
            RoomCategoryService::class,
            RoomVisitorService::class,
            MicrophoneService::class,
            BanRoomService::class,
            BackgroundService::class,
            RoomSalaryService::class,
            PkService::class,
        ];
    }
}
