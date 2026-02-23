<?php

namespace Utd\Vip;

use App\Contracts\OvipRepositoryContract;
use App\Contracts\UserVipRepositoryContract;
use App\Contracts\VipCommonContract;
use Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Vip\Entities\Vip;
use Utd\Vip\Helpers\VipCommon;
use Utd\Vip\Observers\VipObserver;
use Utd\Vip\Repositories\OvipRepository;
use Utd\Vip\Repositories\UserVipRepository;

class VipServiceProvider extends ServiceProvider
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

        // Register observer
        Vip::observe(VipObserver::class);
        $this->loadRoutesFrom(__DIR__.'/../Routes/utd.php');

        Route::prefix('api/dashboard')
            ->middleware(['api'])
            ->group(__DIR__.'/../Routes/dashboard.php');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(VipCommonContract::class, VipCommon::class);
        $this->app->singleton(OvipRepositoryContract::class, OvipRepository::class);
        $this->app->singleton(UserVipRepositoryContract::class, UserVipRepository::class);
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
            __DIR__.'/../Config/config.php' => config_path('vip.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'vip');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/vip');
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', 'vip-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'vip');
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/vip');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'vip');
        } else {
            $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'vip');
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path.'/modules/vip')) {
                $paths[] = $path.'/modules/vip';
            }
        }

        return $paths;
    }
}
