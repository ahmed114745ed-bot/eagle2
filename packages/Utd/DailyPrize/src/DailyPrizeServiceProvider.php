<?php

namespace Utd\DailyPrize;

use Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DailyPrizeServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DailyPrize';

    protected string $moduleNameLower = 'dailyprize';

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
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
            ->group(__DIR__.'/../routes/api.php');

        Route::middleware('web')
            ->group(__DIR__.'/../routes/web.php');

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->group(__DIR__.'/../routes/utd.php');
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/dailyprize.php' => config_path($this->moduleNameLower.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../config/dailyprize.php', $this->moduleNameLower
        );
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
