<?php

namespace Utd\RoomCup;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RoomCupServiceProvider extends ServiceProvider
{
    protected $moduleName = 'RoomCup';

    protected $moduleNameLower = 'roomcup';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/roomcup.php', 'roomcup');
    }

    /**
     * Boot the application events.
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->group(__DIR__.'/../Routes/api.php');

        Route::group([
            'prefix' => config('admin.route.prefix'),
            'middleware' => ['web', 'admin'],
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        });
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-views']);

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\CalculateRoomCupRewards::class,
            ]);
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config/roomcup.php' => config_path('roomcup.php'),
            ], 'roomcup-config');

            $this->publishes([
                __DIR__.'/../Database/migrations' => database_path('migrations'),
            ], 'roomcup-migrations');

            $this->publishes([
                __DIR__.'/../Database/seeders' => database_path('seeders/RoomCup'),
            ], 'roomcup-seeders');
        }
    }
}
