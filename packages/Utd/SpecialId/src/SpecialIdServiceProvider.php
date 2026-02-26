<?php

namespace Utd\SpecialId;

use Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SpecialIdServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'SpecialId';

    protected string $moduleNameLower = 'specialid';

    protected string $namespace = 'Utd\\SpecialId\\Http\\Controllers';

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerUtdRoutes();
        $this->registerDashboardRoutes();
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function register(): void
    {
        //
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = __DIR__.'/../resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', $this->moduleNameLower);
        }
    }

    public function provides(): array
    {
        return [];
    }

    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__.'/../routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__.'/../routes/web.php');
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/specialid.php' => config_path($this->moduleNameLower.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../config/specialid.php', $this->moduleNameLower
        );
    }

    protected function registerUtdRoutes(): void
    {
        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->group(__DIR__.'/../routes/utd.php');
    }

    protected function registerDashboardRoutes(): void
    {
        Route::prefix('api/dashboard')
            ->middleware(['api', 'auth:sanctum', 'verified'])
            ->group(__DIR__.'/../routes/dashboard.php');
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }
}
