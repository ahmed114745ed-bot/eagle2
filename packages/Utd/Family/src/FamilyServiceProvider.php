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
        // Register configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/family.php', 'family'
        );

        $this->registerAliases();

        // Bind Repositories
        $this->app->singleton(\Utd\Family\Repositories\FamilyRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyUserRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyRankRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyLevelRepository::class);

        // Bind Services
        $this->app->singleton(\App\Contracts\FamilyContract::class, \Utd\Family\Services\FamilyService::class);
        $this->app->singleton(\Utd\Family\Services\FamilyService::class);
        $this->app->singleton(\Utd\Family\Services\FamilyLevelService::class);
    }

    protected function registerAliases(): void
    {
        // For Controller inheritance
        if (!class_exists('Utd\Family\Http\Controllers\Controller')) {
            class_alias(config('family.controllers.base', \App\Http\Controllers\Controller::class), 'Utd\Family\Http\Controllers\Controller');
        }

        // For Repository inheritance
        if (!class_exists('Utd\Family\Repositories\AbstractRepository')) {
            class_alias(config('family.repositories.abstract', \App\Tik\Repositories\AbstractRepository::class), 'Utd\Family\Repositories\AbstractRepository');
        }

        // For Room Repository Contract (used in FamilyService constructor)
        if (!interface_exists('Utd\Family\Services\RoomRepositoryContract')) {
            class_alias(config('family.contracts.room_repository', \App\Contracts\RoomRepositoryContract::class), 'Utd\Family\Services\RoomRepositoryContract');
        }

        // For Dashboard Trait
        if (!interface_exists('Utd\Family\Traits\DashBoardTrait')) {
            class_alias(config('family.traits.dashboard', \App\Traits\Dashboard\DashBoardTrait::class), 'Utd\Family\Traits\DashBoardTrait');
        }
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Observe Models
        \Utd\Family\Entities\Family::observe(\Utd\Family\Observers\FamilyObserver::class);
        \Utd\Family\Entities\FamilyUser::observe(\Utd\Family\Observers\FamilyUserObserver::class);

        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        if (file_exists(__DIR__ . '/../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        }

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load Translations (if any)
        // $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'family');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'family');

        // Publishing files
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Utd\Family\Console\InstallFamilyCommand::class,
                \Utd\Family\Console\UninstallFamilyCommand::class,
            ]);

            // Publishing configuration
            $this->publishes([
                __DIR__ . '/../config/family.php' => config_path('family.php'),
            ], 'family-config');

            // Publishing migrations
            // $this->publishes([
            //     __DIR__ . '/../database/migrations/' => database_path('migrations'),
            // ], 'family-migrations');
        }
    }
}
