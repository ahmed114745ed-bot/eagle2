<?php

namespace Utd\LuckyBox;

use Illuminate\Support\ServiceProvider;

class LuckyBoxServiceProvider extends ServiceProvider
{
    protected string $packageName = 'luckybox';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/utd.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path($this->packageName.'.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            $this->packageName
        );
    }
}
