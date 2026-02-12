<?php

namespace Utd\Moments;

use App\Contracts\MomentContract;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Moments\Services\MomentService;

class MomentsServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Moments\\Http\\Controllers';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        //        $this->mergeConfigFrom(__DIR__ . '/../Config/moments.php', 'moments');

        $this->app->singleton(MomentContract::class, MomentService::class);
    }

    /**
     * Boot the application events.
     */
    public function boot(Router $router): void
    {
        //        $router->aliasMiddleware('moment.allowed', CheckAllowedMoment::class);

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
            ->group(__DIR__.'/../Routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__.'/../Routes/web.php');

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->namespace)
            ->group(__DIR__.'/../Routes/utd.php');
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'moments');
    }

    /**
     * Register the package translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'moments');
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config/moments.php' => config_path('moments.php'),
            ], 'moments');

            //            $this->publishes([
            //                __DIR__ . '/../database/migrations' => database_path('migrations'),
            //            ], 'moments');
            //
            //            $this->publishes([
            //                __DIR__ . '/../database/seeders' => database_path('seeders/Moments'),
            //            ], 'moments');
            //
            //            $this->publishes([
            //                __DIR__ . '/../resources/views' => resource_path('views/vendor/moments'),
            //            ], 'moments');
            //
            //            $this->publishes([
            //                __DIR__ . '/../resources/lang' => lang_path('vendor/moments'),
            //            ], 'moments');
        }
    }
}
