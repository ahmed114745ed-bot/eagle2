<?php

namespace Utd\Agency;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Agency\Console\InstallAgencyCommand;
use Utd\Agency\Console\UninstallAgencyCommand;

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
        $this->registerCommands();
        $this->mergeConfigFrom(__DIR__ . '/../Config/agency.php', 'agency-package');

        // Register Model Aliases
        $this->app->alias(
            \Utd\Agency\Entities\UsersJoinedAgency::class,
            'App\Models\UsersJoinedAgency'
        );

        // Register Services - Safe binding (returns null if not exists)
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyServiceInterface::class,
            function ($app) {
                if (class_exists(\Utd\Agency\Services\AgencyService::class)) {
                    return $app->make(\Utd\Agency\Services\AgencyService::class);
                }
                return null;
            }
        );

        // Register Repositories - Safe binding (returns null if not exists)
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyRepositoryInterface::class,
            function ($app) {
                if (class_exists(\Utd\Agency\Repositories\AgencyRepository::class)) {
                    return $app->make(\Utd\Agency\Repositories\AgencyRepository::class);
                }
                return null;
            }
        );
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        // Register class alias for backward compatibility
        if (!class_exists('App\Models\UsersJoinedAgency')) {
            class_alias(
                \Utd\Agency\Entities\UsersJoinedAgency::class,
                'App\Models\UsersJoinedAgency'
            );
        }

        $this->registerRoutes();
        $this->registerShippingRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();

        // Apply Host Filter Global Scope for Admin Dashboard
        try {
            if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
                if (request()->is(config('admin.route.prefix') . '*')) {
                    \App\Models\User::addGlobalScope('is_host', function (\Illuminate\Database\Eloquent\Builder $builder) {
                        $builder->where('is_host', 1);
                    });
                }
            }
        } catch (\Exception $e) {
            // Prevent boot failures if helper or config is missing
        }
    }

    /**
     * Register the Host Agency package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        // API Routes
        if (config('agency-package.routes.api_enabled', true)) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../Routes/api.php');
        }

        // Web/Admin Routes (requires Laravel Admin)
        if (config('agency-package.routes.web_enabled', true) && $this->isLaravelAdminInstalled()) {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../Routes/web.php');
        }

        // UTD API Routes
        if (config('agency-package.routes.utd_enabled', true)) {
            Route::prefix('api/utd')
                ->middleware(['api', 'localization'])
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../Routes/utd.php');
        }
    }

    /**
     * Register the Shipping Agency package routes.
     *
     * @return void
     */
    protected function registerShippingRoutes(): void
    {
        // Shipping API Routes
        if (config('agency-package.routes.shipping_api_enabled', true)) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->shippingNamespace)
                ->group(__DIR__ . '/../Routes/shipping-api.php');
        }

        // Shipping Web/Admin Routes (requires Laravel Admin)
        if (config('agency-package.routes.shipping_web_enabled', true) && $this->isLaravelAdminInstalled()) {
            Route::middleware('web')
                ->namespace($this->shippingNamespace)
                ->group(__DIR__ . '/../Routes/shipping-web.php');
        }

        // Shipping UTD API Routes
        if (config('agency-package.routes.shipping_utd_enabled', true)) {
            Route::prefix('api/utd')
                ->middleware(['api', 'localization'])
                ->namespace($this->shippingNamespace)
                ->group(__DIR__ . '/../Routes/shipping-utd.php');
        }
    }

    /**
     * Check if Laravel Admin (Encore) is installed.
     *
     * @return bool
     */
    protected function isLaravelAdminInstalled(): bool
    {
        return class_exists(\Encore\Admin\Admin::class);
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

    /**
     * Register the package's console commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallAgencyCommand::class,
            UninstallAgencyCommand::class,
        ]);
    }
}
