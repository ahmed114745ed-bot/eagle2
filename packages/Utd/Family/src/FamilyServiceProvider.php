<?php

namespace Utd\Family;

use App\Contracts\FamilyContract;
use Illuminate\Support\ServiceProvider;

class FamilyServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/family.php', 'family'
        );

        if (! $this->isEnabled()) {
            return;
        }

        $this->registerAliases();

        // Clean contract binding — like Moments pattern
        $this->app->singleton(FamilyContract::class, Services\FamilyService::class);

        $this->app->singleton(Repositories\FamilyRepository::class);
        $this->app->singleton(Repositories\FamilyUserRepository::class);
        $this->app->singleton(Repositories\FamilyRankRepository::class);
        $this->app->singleton(Repositories\FamilyLevelRepository::class);
        $this->app->singleton(Services\FamilyLevelService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        Entities\Family::observe(Observers\FamilyObserver::class);
        Entities\FamilyUser::observe(Observers\FamilyUserObserver::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        if (file_exists(__DIR__.'/../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'family');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallFamilyCommand::class,
                Console\UninstallFamilyCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/family.php' => config_path('family.php'),
            ], 'family-config');
        }
    }

    protected function registerAliases(): void
    {
        if (! class_exists('Utd\\Family\\Http\\Controllers\\Controller')) {
            $baseController = config('family.controllers.base');
            if ($baseController && class_exists($baseController)) {
                class_alias($baseController, 'Utd\\Family\\Http\\Controllers\\Controller');
            }
        }

        if (! class_exists('Utd\\Family\\Http\\Controllers\\Admin\\MainController')) {
            $mainController = config('family.controllers.admin_main');
            if ($mainController && class_exists($mainController)) {
                class_alias($mainController, 'Utd\\Family\\Http\\Controllers\\Admin\\MainController');
            }
        }

        if (! class_exists('Utd\\Family\\Repositories\\AbstractRepository')) {
            $abstractRepository = config('family.repositories.abstract');
            if ($abstractRepository && class_exists($abstractRepository)) {
                class_alias($abstractRepository, 'Utd\\Family\\Repositories\\AbstractRepository');
            }
        }

        $roomRepositoryContract = config('family.contracts.room_repository');
        if ($roomRepositoryContract) {
            if (! interface_exists('Utd\\Family\\Services\\RoomRepositoryContract')) {
                if ($roomRepositoryContract && interface_exists($roomRepositoryContract)) {
                    class_alias($roomRepositoryContract, 'Utd\\Family\\Services\\RoomRepositoryContract');
                }
            }

            $this->app->bind('Utd\\Family\\Services\\RoomRepositoryContract', function ($app) use ($roomRepositoryContract) {
                return $app->make($roomRepositoryContract);
            });
        }

        if (! trait_exists('Utd\\Family\\Traits\\DashBoardTrait', false)) {
            $dashboardTrait = config('family.traits.dashboard');
            if ($dashboardTrait && trait_exists($dashboardTrait)) {
                class_alias($dashboardTrait, 'Utd\\Family\\Traits\\DashBoardTrait');
            }
        }
    }

    protected function isEnabled(): bool
    {
        return (bool) config('family.enabled', true);
    }
}
