<?php

namespace Utd\Moments;

use App\Contracts\MomentContract;
use App\Services\Null\NullMomentService;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Moments\Http\Middleware\CheckAllowedMoment;
use Utd\Moments\Services\MomentService;

class MomentsServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Moments\\Http\\Controllers';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/moments.php', 'moments');
        $this->mergeConfigFrom(__DIR__ . '/../config/viewer.php', 'moments.viewer');

        // Register MomentService based on license validation
//        $this->app->singleton(MomentContract::class, function ($app) {
//            if ($this->isLicenseValid()) {
//                return $app->make(MomentService::class);
//            }
//
//            return $app->make(NullMomentService::class);
//        });
        $this->app->singleton(MomentContract::class, MomentService::class);
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $router->aliasMiddleware('moment.allowed', CheckAllowedMoment::class);

        $this->registerRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/api.php');

        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/web.php');

        Route::prefix('api/Utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/utd.php');
    }

    /**
     * Register the package views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'moments');
    }

    /**
     * Register the package translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'moments');
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/moments.php' => config_path('moments.php'),
                __DIR__ . '/../config/viewer.php' => config_path('moments-viewer.php'),
            ], 'moments');

//            $this->publishes([
//                __DIR__ . '/../database/migrations' => database_path('migrations'),
//            ], 'moments');

            $this->publishes([
                __DIR__ . '/../database/seeders' => database_path('seeders/Moments'),
            ], 'moments');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/moments'),
            ], 'moments');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/moments'),
            ], 'moments');
        }
    }

    /**
     * Check if the package license is valid.
     *
     * @return bool
     */
    protected function isLicenseValid(): bool
    {
        $licenseKey = config('moments.license_key');

        if (empty($licenseKey)) {
            return false;
        }

        $domain = request()->getHost();
        $expectedHash = hash('sha256', $domain . config('moments.license_secret'));

        return $licenseKey === $expectedHash;
    }
}
