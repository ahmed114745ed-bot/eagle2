<?php

namespace Utd\Agency;

use Exception;
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
     */
    public function register(): void
    {
        $this->registerCommands();
        $this->mergeConfigFrom(__DIR__.'/../Config/agency.php', 'agency-package');
        $this->mergeConfigFrom(__DIR__.'/../Config/agency-dependencies.php', 'agency-dependencies');

        // Register class aliases for backward compatibility early
        $this->registerModelAliases();

        // Register Service Contracts
        $this->registerServiceContracts();

        // Register Helper Services
        $this->registerHelperServices();
    }

    /**
     * Boot the application events.
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerShippingRoutes();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerMigrations();
        $this->registerPublishing();

        // Apply Host Filter Global Scope for Admin Dashboard
        try {
            if (\App\Helpers\AgencyPackageHelper::isAgencyInstalled()) {
                if (request()->is(config('admin.route.prefix').'*')) {
                    \App\Models\User::addGlobalScope('is_host', function (\Illuminate\Database\Eloquent\Builder $builder) {
                        $builder->where('is_host', 1);
                    });
                }
            }
        } catch (Exception $e) {
            // Prevent boot failures if helper or config is missing
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            Contracts\AgencyServiceInterface::class,
            Contracts\AgencyRepositoryInterface::class,
        ];
    }

    /**
     * Register Service Contracts
     */
    protected function registerServiceContracts(): void
    {
        // Agency Service
        $this->app->bind(
            Contracts\AgencyServiceInterface::class,
            function ($app) {
                $serviceClass = config('agency-dependencies.dependencies.services.agency_service');
                if ($serviceClass && class_exists($serviceClass)) {
                    try {
                        return $app->make($serviceClass);
                    } catch (Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Agency Package: Failed to resolve service \'agency_service\'', [
                            'error' => $e->getMessage(),
                        ]);

                        return new Services\NullAgencyService();
                    }
                }

                return new Services\NullAgencyService();
            }
        );

        // Charge Service
        $this->app->bind(
            Contracts\ChargeServiceInterface::class,
            function ($app) {
                $serviceClass = config('agency-dependencies.dependencies.services.charge_service');
                if ($serviceClass && class_exists($serviceClass)) {
                    return $app->make($serviceClass);
                }

                return new Services\NullChargeService();
            }
        );

        // Agency Host Invite Service
        $this->app->bind(
            Contracts\AgencyHostInviteServiceInterface::class,
            function ($app) {
                $serviceClass = config('agency-dependencies.dependencies.services.agency_host_invite_service');
                if ($serviceClass && class_exists($serviceClass)) {
                    return $app->make($serviceClass);
                }

                return null;
            }
        );

        // User Achievement Service
        $this->app->bind(
            Contracts\UserAchievementServiceInterface::class,
            function ($app) {
                // Check if App\Contracts\UserAchievementContract exists and is bound
                if (interface_exists(\App\Contracts\UserAchievementContract::class)
                    && $app->bound(\App\Contracts\UserAchievementContract::class)) {
                    return $app->make(\App\Contracts\UserAchievementContract::class);
                }

                return new Services\NullUserAchievementService();
            }
        );

        // REMOVED: Backward compatibility binding was causing circular dependency
        // App\Contracts\UserAchievementContract should be bound by Achievements package only
    }

    /**
     * Register Helper Services
     */
    protected function registerHelperServices(): void
    {
        // Register Helper Service
        $this->app->singleton('agency.helper', function ($app) {
            return new Services\AgencyHelperService();
        });

        // Register Models Helper
        $this->app->singleton('agency.models', function ($app) {
            return new Helpers\AgencyModelsHelper();
        });

        // Register External Model Service
        $this->app->singleton('agency.external.model', function ($app) {
            return new Services\ExternalModelResolver();
        });

        // Register External Module Service
        $this->app->singleton('agency.external.module', function ($app) {
            return new Services\ExternalModuleResolver();
        });

        // Bind ExternalModuleInterface to ExternalModuleResolver
        $this->app->bind(
            Contracts\ExternalModuleInterface::class,
            function ($app) {
                return $app->make('agency.external.module');
            }
        );

        // Register External Helper Service
        $this->app->singleton('agency.external.helper', function ($app) {
            return new Services\ExternalHelperResolver();
        });

        // Register Repository Contracts
        $this->app->bind(
            Contracts\AgencyRepositoryInterface::class,
            function ($app) {
                if (class_exists(Repositories\AgencyRepository::class)) {
                    return $app->make(Repositories\AgencyRepository::class);
                }

                return null;
            }
        );

        // Bind ShippingAgencyRepository to its interface for backward compatibility
        $this->app->bind(
            \App\Contracts\ShippingAgencyRepositoryInterface::class,
            Repositories\ShippingAgencyRepository::class
        );
    }

    /**
     * Register the Host Agency package routes.
     */
    protected function registerRoutes(): void
    {
        // API Routes
        if (config('agency-package.routes.api_enabled', true)) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(__DIR__.'/../Routes/api.php');
        }

        // Web/Admin Routes (requires Laravel Admin)
        if (config('agency-package.routes.web_enabled', true) && $this->isLaravelAdminInstalled()) {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(__DIR__.'/../Routes/web.php');
        }

        // UTD API Routes
        if (config('agency-package.routes.utd_enabled', true)) {
            Route::prefix('api/utd')
                ->middleware(['api', 'localization'])
                ->namespace($this->namespace)
                ->group(__DIR__.'/../Routes/utd.php');
        }
    }

    /**
     * Register the Shipping Agency package routes.
     */
    protected function registerShippingRoutes(): void
    {
        // Shipping API Routes
        if (config('agency-package.routes.shipping_api_enabled', true)) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->shippingNamespace)
                ->group(__DIR__.'/../Routes/shipping-api.php');
        }

        // Shipping Web/Admin Routes (requires Laravel Admin)
        if (config('agency-package.routes.shipping_web_enabled', true) && $this->isLaravelAdminInstalled()) {
            Route::middleware('web')
                ->namespace($this->shippingNamespace)
                ->group(__DIR__.'/../Routes/shipping-web.php');
        }

        // Shipping UTD API Routes
        if (config('agency-package.routes.shipping_utd_enabled', true)) {
            Route::prefix('api/utd')
                ->middleware(['api', 'localization'])
                ->namespace($this->shippingNamespace)
                ->group(__DIR__.'/../Routes/shipping-utd.php');
        }
    }

    /**
     * Check if Laravel Admin (Encore) is installed.
     */
    protected function isLaravelAdminInstalled(): bool
    {
        return class_exists(\Encore\Admin\Admin::class);
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'agency');
    }

    /**
     * Register the package translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'agency');
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
                __DIR__.'/../Config/agency.php' => config_path('agency-package.php'),
            ], 'agency-config');

            $this->publishes([
                __DIR__.'/../Resources/views' => resource_path('views/vendor/agency'),
            ], 'agency-views');

            $this->publishes([
                __DIR__.'/../Database/Migrations' => database_path('migrations'),
            ], 'agency-migrations');

            $this->publishes([
                __DIR__.'/../Resources/lang' => resource_path('lang/vendor/agency'),
            ], 'agency-lang');
        }
    }

    /**
     * Register the package's console commands.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallAgencyCommand::class,
            UninstallAgencyCommand::class,
        ]);
    }

    /**
     * Register model aliases for backward compatibility.
     */
    protected function registerModelAliases(): void
    {
        $aliases = [
            'UsersJoinedAgency' => Entities\UsersJoinedAgency::class,
            'Agency' => Entities\Agency::class,
            'ShippingAgency' => Entities\ShippingAgency::class,
            'AgencyJoinRequest' => Entities\AgencyJoinRequest::class,
            'AgencySallary' => Entities\AgencySalary::class,
            'AgencyUserJob' => Entities\AgencyUserJob::class,
            'AdditionalInfo' => Entities\AdditionalInfo::class,
            'LeaveAgencyRequest' => Entities\LeaveAgencyRequest::class,
            'HostAgencyInvite' => Entities\HostAgencyInvite::class,
        ];

        foreach ($aliases as $alias => $class) {
            if (! class_exists('App\\Models\\'.$alias)) {
                class_alias($class, 'App\\Models\\'.$alias);
            }
        }
    }
}
