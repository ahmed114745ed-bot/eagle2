<?php

namespace Utd\Bd;

use Illuminate\Support\ServiceProvider;

class BdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bd.php', 'bd');
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/bd.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bd');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bd.php' => config_path('bd.php'),
            ], 'bd-config');
        }
    }
}
