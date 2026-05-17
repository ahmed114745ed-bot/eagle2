<?php

namespace Utd\AreaManager;

use Illuminate\Support\ServiceProvider;

class AreaManagerServiceProvider extends ServiceProvider
{
    protected $moduleNameLower = 'areamanager';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/area_manager.php', 'area_manager');
    }

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerTranslations();
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/area_manager.php' => config_path('area_manager.php'),
        ], 'area-manager-config');
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/packages/areamanager');
        $sourcePath = __DIR__.'/../resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], 'area-manager-views');

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/packages/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', $this->moduleNameLower);
        }
    }
}
