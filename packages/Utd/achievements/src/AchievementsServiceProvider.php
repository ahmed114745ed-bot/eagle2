<?php

namespace Utd\Achievements;

use App\Contracts\AchievementContract;
use App\Contracts\AchievementLevelContract;
use App\Contracts\UserAchievementContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\Achievements\Services\AchievementService;
use Utd\Achievements\Services\AchievementLevelsService;
use Utd\Achievements\Services\UserAchievementService;
use Utd\Achievements\Console\ResetUserAchievementMonthly;

class AchievementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/achievements.php', 'achievements');

        $this->app->singleton(AchievementContract::class, AchievementService::class);
        $this->app->singleton(AchievementLevelContract::class, AchievementLevelsService::class);
        $this->app->singleton(UserAchievementContract::class, UserAchievementService::class);
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerCommands();
        $this->registerRoutes();
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/achievements.php' => config_path('achievements.php'),
            ], 'achievements-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'achievements-migrations');

//            $this->publishes([
//                __DIR__ . '/../database/seeders' => database_path('seeders/Achievements'),
//            ], 'achievements-seeders');
//
//            $this->publishes([
//                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/achievements'),
//            ], 'achievements-lang');
        }
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register the package translations.
     */
    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'achievements');
    }

    /**
     * Register the package console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ResetUserAchievementMonthly::class,
            ]);
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('achievements.routes.api_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        if (config('achievements.routes.utd_enabled', true)) {
            Route::prefix('api/utd')
                ->middleware(['api', 'localization'])
                ->group(__DIR__ . '/../routes/utd.php');
        }

        if (config('achievements.routes.web_enabled', true) && $this->isLaravelAdminInstalled()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/../routes/preview.php');
        }
    }

    /**
     * Check if Laravel Admin (Encore) is installed.
     */
    protected function isLaravelAdminInstalled(): bool
    {
        return class_exists(\Encore\Admin\Admin::class);
    }
}
