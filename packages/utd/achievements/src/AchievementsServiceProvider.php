<?php

namespace Utd\Achievements;

use App\Contracts\AchievementContract;
use Illuminate\Support\ServiceProvider;
use Utd\Achievements\Services\AchievementService;
use Utd\Achievements\Null\NullAchievementService;
use Utd\Achievements\Console\ResetUserAchievementMonthly;

class AchievementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/achievements.php', 'achievements');

        // Package binds directly to the Contract!
        $this->app->singleton(AchievementContract::class, function ($app) {
            // Check license
            if ($this->isLicenseValid()) {
                return new AchievementService();
            }

            // Not licensed - return package's own null service
            return new NullAchievementService();
        });
    }

    public function boot(): void
    {
        // ========================================
        // PUBLISH CONFIGURATION
        // ========================================
        $this->publishes([
            __DIR__ . '/../config/achievements.php' => config_path('achievements.php'),
        ], 'achievements-config');

        // ========================================
        // MIGRATIONS
        // ========================================
        // Publish migrations for users who want to customize
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'achievements-migrations');

        // Auto-load migrations (no need to publish)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // ========================================
        // SEEDERS (Publishable)
        // ========================================
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders/Achievements'),
        ], 'achievements-seeders');

        // ========================================
        // TRANSLATIONS
        // ========================================
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'achievements');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/achievements'),
        ], 'achievements-lang');

        // ========================================
        // CONSOLE COMMANDS
        // ========================================
        if ($this->app->runningInConsole()) {
            $this->commands([
                ResetUserAchievementMonthly::class,
            ]);
        }

        // ========================================
        // API ROUTES
        // ========================================
        if (config('achievements.routes.api_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        // ========================================
        // WEB/ADMIN ROUTES INTEGRATION
        // ========================================
        // Load web routes (Laravel Admin) if:
        // 1. Laravel Admin (Encore) is installed
        // 2. Web routes are enabled in config
        if (config('achievements.routes.web_enabled', true) && $this->isLaravelAdminInstalled()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }
    }

    /**
     * Check if Laravel Admin (Encore) is installed
     */
    protected function isLaravelAdminInstalled(): bool
    {
        return class_exists(\Encore\Admin\Admin::class);
    }

    /**
     * Validate license key
     */
    protected function isLicenseValid(): bool
    {
        $licenseKey = config('achievements.license_key');

        if (empty($licenseKey)) {
            return false;
        }

        // Simple validation - domain hash
        $domain = request()->getHost();
        $expectedHash = hash('sha256', $domain . config('achievements.secret'));

        if ($licenseKey === $expectedHash) {
            return true;
        }

        return false;
    }
}
