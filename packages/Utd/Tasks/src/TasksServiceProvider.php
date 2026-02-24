<?php

namespace Utd\Tasks;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class TasksServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerTranslations();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
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
        $configPath = __DIR__.'/../Config/config.php';
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('tasks.php'),
            ], 'config');

            $this->mergeConfigFrom($configPath, 'tasks');
        }
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/tasks');
        $sourcePath = __DIR__.'/../Resources/views';

        if (is_dir($sourcePath)) {
            $this->publishes([
                $sourcePath => $viewPath,
            ], ['views', 'tasks-module-views']);

            $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'tasks');
        }
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/tasks');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'tasks');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'tasks');
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/tasks')) {
                $paths[] = $path.'/modules/tasks';
            }
        }

        return $paths;
    }
}
