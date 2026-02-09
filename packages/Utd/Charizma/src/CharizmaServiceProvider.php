<?php

namespace Utd\Charizma;

use App\Contracts\UserCharismaServiceContract;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Charizma\Services\UserCharismaService;

class CharizmaServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Charizma';
    protected string $moduleNameLower = 'charizma';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/charizma.php', 'charizma');
        
        $this->app->singleton(UserCharismaServiceContract::class, UserCharismaService::class);
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
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-views']);

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    /**
     * Register publishing.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/charizma.php' => config_path('charizma.php'),
            ], 'charizma-config');

            $this->publishes([
                __DIR__ . '/../Database/migrations' => database_path('migrations'),
            ], 'charizma-migrations');
        }
    }
}
