<?php

namespace Utd\Badge;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BadgeServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Badge\\Http\\Controllers';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'badge');
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
    }

    /**
     * Register the package routes.
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
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'badge');
    }

    /**
     * Register the package translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'badge');
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/config.php' => config_path('badge.php'),
            ], 'badge');
        }
    }
}
