<?php

namespace Utd\RoomBoom;

use App\Contracts\NewRoomBoomGiftServiceContract;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\RoomBoom\Entities\RoomBoomLevel;
use Utd\RoomBoom\Observers\RoomBoomLevelObserver;
use Utd\RoomBoom\Services\NewRoomBoomGiftService;

class RoomBoomServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\RoomBoom\\Http\\Controllers';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'roomboom');

        $this->app->singleton(NewRoomBoomGiftServiceContract::class, NewRoomBoomGiftService::class);
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
        $this->registerObservers();
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
            ->middleware('room.boom')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Register the package views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'roomboom');
    }

    /**
     * Register the package translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'roomboom');
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
            $this->publishes([
                __DIR__ . '/../Config/config.php' => config_path('roomboom.php'),
            ], 'roomboom-config');

            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/roomboom'),
            ], 'roomboom-views');
        }
    }

    /**
     * Register model observers.
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        RoomBoomLevel::observe(RoomBoomLevelObserver::class);
    }
}
