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
        $this->mergeConfigFrom(__DIR__ . '/../Config/agency-dependencies.php', 'agency-dependencies');

        // Register class aliases for backward compatibility early
        $this->registerModelAliases();
        
        // Register Service Contracts
        $this->registerServiceContracts();
        
        // Register Helper Services
        $this->registerHelperServices();
    }
    
    /**
     * Register Service Contracts
     */
    protected function registerServiceContracts(): void
    {
        // Agency Service
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyServiceInterface::class,
            function ($app) {
                $serviceClass = config('agency-dependencies.dependencies.services.agency_service');
                if ($serviceClass && class_exists($serviceClass)) {
                    return $app->make($serviceClass);
                }
                return new \Utd\Agency\Services\NullAgencyService();
            }
        );
        
        // Charge Service
        $this->app->bind(
            \Utd\Agency\Contracts\ChargeServiceInterface::class,
            function ($app) {
                $serviceClass = config('agency-dependencies.dependencies.services.charge_service');
                if ($serviceClass && class_exists($serviceClass)) {
                    return $app->make($serviceClass);
                }
                return new \Utd\Agency\Services\NullChargeService();
            }
        );
        
        // Agency Host Invite Service
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyHostInviteServiceInterface::class,
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
            \Utd\Agency\Contracts\UserAchievementServiceInterface::class,
            function ($app) {
                // Check if App\Contracts\UserAchievementContract exists
                if (interface_exists(\App\Contracts\UserAchievementContract::class)) {
                    return $app->make(\App\Contracts\UserAchievementContract::class);
                }
                return new \Utd\Agency\Services\NullUserAchievementService();
            }
        );
        
        // Backward compatibility - bind old contract name to new one
        if (interface_exists(\App\Contracts\UserAchievementContract::class)) {
            $this->app->bind(
                \App\Contracts\UserAchievementContract::class,
                function ($app) {
                    return $app->make(\Utd\Agency\Contracts\UserAchievementServiceInterface::class);
                }
            );
        }
    }
    
    /**
     * Register Helper Services
     */
    protected function registerHelperServices(): void
    {
        // Register Helper Service
        $this->app->singleton('agency.helper', function ($app) {
            return new \Utd\Agency\Services\AgencyHelperService();
        });
        
        // Register Models Helper
        $this->app->singleton('agency.models', function ($app) {
            return new \Utd\Agency\Helpers\AgencyModelsHelper();
        });
        
        // Register External Model Service
        $this->app->singleton('agency.external.model', function ($app) {
            return new \Utd\Agency\Services\ExternalModelResolver();
        });
        
        // Register External Module Service
        $this->app->singleton('agency.external.module', function ($app) {
            return new \Utd\Agency\Services\ExternalModuleResolver();
        });
        
        // Bind ExternalModuleInterface to ExternalModuleResolver
        $this->app->bind(
            \Utd\Agency\Contracts\ExternalModuleInterface::class,
            function ($app) {
                return $app->make('agency.external.module');
            }
        );
        
        // Register External Helper Service
        $this->app->singleton('agency.external.helper', function ($app) {
            return new \Utd\Agency\Services\ExternalHelperResolver();
        });
        
        // Register Repository Contracts
        $this->app->bind(
            \Utd\Agency\Contracts\AgencyRepositoryInterface::class,
            function ($app) {
                if (class_exists(\Utd\Agency\Repositories\AgencyRepository::class)) {
                    return $app->make(\Utd\Agency\Repositories\AgencyRepository::class);
                }
                return null;
            }
        );
        
        // Bind ShippingAgencyRepository to its interface for backward compatibility
        $this->app->bind(
            \App\Contracts\ShippingAgencyRepositoryInterface::class,
            \Utd\Agency\Repositories\ShippingAgencyRepository::class
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

    /**
     * Register model aliases for backward compatibility.
     *
     * @return void
     */
    protected function registerModelAliases(): void
    {
        $aliases = [
            'UsersJoinedAgency' => \Utd\Agency\Entities\UsersJoinedAgency::class,
            'Agency' => \Utd\Agency\Entities\Agency::class,
            'ShippingAgency' => \Utd\Agency\Entities\ShippingAgency::class,
            'AgencyJoinRequest' => \Utd\Agency\Entities\AgencyJoinRequest::class,
            'AgencySallary' => \Utd\Agency\Entities\AgencySalary::class,
            'AgencyUserJob' => \Utd\Agency\Entities\AgencyUserJob::class,
            'AdditionalInfo' => \Utd\Agency\Entities\AdditionalInfo::class,
            'LeaveAgencyRequest' => \Utd\Agency\Entities\LeaveAgencyRequest::class,
            'HostAgencyInvite' => \Utd\Agency\Entities\HostAgencyInvite::class,
        ];

        foreach ($aliases as $alias => $class) {
            if (!class_exists('App\\Models\\' . $alias)) {
                class_alias($class, 'App\\Models\\' . $alias);
            }
        }
    }
}
