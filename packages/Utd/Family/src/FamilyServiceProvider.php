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

        // Bind Repositories
        $this->app->singleton(\Utd\Family\Repositories\FamilyRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyUserRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyRankRepository::class);
        $this->app->singleton(\Utd\Family\Repositories\FamilyLevelRepository::class);

        // Bind Services
        $this->app->singleton(\Utd\Family\Services\FamilyService::class);
        $this->app->singleton(\Utd\Family\Services\FamilyLevelService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Observe Models
        \Utd\Family\Models\Family::observe(\Utd\Family\Observers\FamilyObserver::class);
        \Utd\Family\Models\FamilyUser::observe(\Utd\Family\Observers\FamilyUserObserver::class);

        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load Translations (if any)
        // $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'family');

        // Load Views (if any)
        // $this->loadViewsFrom(__DIR__ . '/../resources/views', 'family');

        // Publishing files
        if ($this->app->runningInConsole()) {
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
