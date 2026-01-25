<?php

namespace Utd\Reals;

use Illuminate\Support\ServiceProvider;
use App\Contracts\RealsContract;
use Utd\Reals\Services\RealsService;

class RealsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // دمج الإعدادات
        $this->mergeConfigFrom(
            __DIR__ . '/../config/reals.php',
            'reals'
        );

        // تسجيل الـ Route Service Provider
        $this->app->register(Providers\RouteServiceProvider::class);

        // ربط الـ Contract بالـ Real Implementation
        // هذا يتجاوز الـ NullRealsService في الـ Base Project
        $this->app->singleton(
            RealsContract::class,
            RealsService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // تحميل الـ Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // تحميل الـ Translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'reals');

        // تحميل الـ Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'reals');

        // نشر الـ Public Assets للمجلد public/modules/reals
        $this->publishes([
            __DIR__ . '/../public' => public_path('modules/reals'),
        ], 'reals-assets');

        // تسجيل الأوامر للـ Console
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerPublishables();
        }
    }

    /**
     * تسجيل الأوامر
     */
    protected function registerCommands(): void
    {
        $this->commands([
            // Console\CleanupOldRealsCommand::class,
            // Console\GenerateThumbnailsCommand::class,
        ]);
    }

    /**
     * تسجيل الملفات القابلة للنشر
     */
    protected function registerPublishables(): void
    {
        // نشر الـ Config
        $this->publishes([
            __DIR__ . '/../config/reals.php' => config_path('reals.php'),
        ], 'reals-config');

        // نشر الـ Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'reals-migrations');

        // نشر الـ Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/reals'),
        ], 'reals-views');

        // نشر الـ Public Assets (JS, CSS)
        $this->publishes([
            __DIR__ . '/../public' => public_path('modules/reals'),
        ], 'reals-assets');

        // نشر كل شيء
        $this->publishes([
            __DIR__ . '/../config/reals.php' => config_path('reals.php'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../public' => public_path('modules/reals'),
        ], 'reals');
    }
}
