<?php

namespace Utd\Agency;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AgencyServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Agency\\Http\\Controllers';
    protected $shippingNamespace = 'Utd\\Agency\\Http\\Controllers\\Shipping';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/agency.php', 'agency-package');

        // Register Services
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyServiceInterface::class,
            \Utd\Agency\Services\AgencyService::class
        );

        // Register Repositories
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyRepositoryInterface::class,
            \Utd\Agency\Repositories\AgencyRepository::class
        );
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerShippingRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the Host Agency package routes.
     *
     * @return void
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

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/utd.php');
    }

    /**
     * Register the Shipping Agency package routes.
     *
     * @return void
     */
    protected function registerShippingRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->shippingNamespace)
            ->group(__DIR__ . '/../Routes/shipping-api.php');

        Route::middleware('web')
            ->namespace($this->shippingNamespace)
            ->group(__DIR__ . '/../Routes/shipping-web.php');

        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->namespace($this->shippingNamespace)
            ->group(__DIR__ . '/../Routes/shipping-utd.php');
    }

    /**
     * Register the package views.
     *
     * @return void
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'agency');
    }

    /**
     * Register the package translations.
     *
     * @return void
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'agency');
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
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
                __DIR__ . '/../Config/agency.php' => config_path('agency-package.php'),
            ], 'agency-config');

            $this->publishes([
                __DIR__ . '/../Resources/views' => resource_path('views/vendor/agency'),
            ], 'agency-views');

            $this->publishes([
                __DIR__ . '/../Database/Migrations' => database_path('migrations'),
            ], 'agency-migrations');

            $this->publishes([
                __DIR__ . '/../Resources/lang' => resource_path('lang/vendor/agency'),
            ], 'agency-lang');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            \Utd\Agency\Contracts\AgencyServiceInterface::class,
            \Utd\Agency\Contracts\AgencyRepositoryInterface::class,
        ];
    }
}
