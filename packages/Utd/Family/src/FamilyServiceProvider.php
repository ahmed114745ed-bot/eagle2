<?php

namespace Utd\Family;

use Illuminate\Support\ServiceProvider;

class FamilyServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/family.php', 'family'
        );

        $this->registerAliases();
        $this->registerFamilyServices();

        $this->app->singleton(\Utd\Family\Repositories\FamilyRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyUserRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyRankRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyLevelRepository::class);
        $this->app->singleton(\Utd\Family\Services\FamilyLevelService::class);
    }

    protected function registerAliases(): void
    {
        if (!class_exists('Utd\\Family\\Http\\Controllers\\Controller')) {
            $baseController = config('family.controllers.base');
            if ($baseController && class_exists($baseController)) {
                class_alias($baseController, 'Utd\\Family\\Http\\Controllers\\Controller');
            }
        }

        if (!class_exists('Utd\\Family\\Http\\Controllers\\Admin\\MainController')) {
            $mainController = config('family.controllers.admin_main');
            if ($mainController && class_exists($mainController)) {
                class_alias($mainController, 'Utd\\Family\\Http\\Controllers\\Admin\\MainController');
            }
        }

        if (!class_exists('Utd\\Family\\Repositories\\AbstractRepository')) {
            $abstractRepository = config('family.repositories.abstract');
            if ($abstractRepository && class_exists($abstractRepository)) {
                class_alias($abstractRepository, 'Utd\\Family\\Repositories\\AbstractRepository');
            }
        }

        if (!interface_exists('Utd\\Family\\Services\\RoomRepositoryContract')) {
            $roomRepositoryContract = config('family.contracts.room_repository');
            if ($roomRepositoryContract && interface_exists($roomRepositoryContract)) {
                class_alias($roomRepositoryContract, 'Utd\\Family\\Services\\RoomRepositoryContract');
            }
        }

        if (!trait_exists('Utd\\Family\\Traits\\DashBoardTrait', false)) {
            $dashboardTrait = config('family.traits.dashboard');
            if ($dashboardTrait && trait_exists($dashboardTrait)) {
                class_alias($dashboardTrait, 'Utd\\Family\\Traits\\DashBoardTrait');
            }
        }
    }

    protected function registerFamilyServices(): void
    {
        $familyService = \Utd\Family\Services\FamilyService::class;
        $nullFamilyService = \Utd\Family\Services\NullFamilyService::class;
        $contractInterface = config('family.contracts.family_service', \Utd\Family\Contracts\FamilyServiceContract::class);

        $this->app->singleton($familyService);
        $this->app->singleton($nullFamilyService);
        $this->app->alias($nullFamilyService, 'family.null_service');

        $this->app->singleton(\Utd\Family\Contracts\FamilyServiceContract::class, function ($app) use ($familyService) {
            return $app->make($familyService);
        });

        if ($contractInterface && interface_exists($contractInterface)) {
            $this->app->singleton($contractInterface, function ($app) use ($familyService) {
                return $app->make($familyService);
            });
        }
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        \Utd\Family\Entities\Family::observe(\Utd\Family\Observers\FamilyObserver::class);
        \Utd\Family\Entities\FamilyUser::observe(\Utd\Family\Observers\FamilyUserObserver::class);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        if (file_exists(__DIR__ . '/../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'family');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Utd\Family\Console\InstallFamilyCommand::class,
                \Utd\Family\Console\UninstallFamilyCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/family.php' => config_path('family.php'),
            ], 'family-config');
        }
    }
}
