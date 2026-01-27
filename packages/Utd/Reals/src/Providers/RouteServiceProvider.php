<?php

namespace Utd\Reals\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // لا تسجل الـ routes إذا لم تكن الجداول موجودة
        if (!$this->tablesExist()) {
            return;
        }

        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * التحقق من وجود جداول الريلز
     */
    protected function tablesExist(): bool
    {
        try {
            return Schema::hasTable('reals');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Define the "api" routes.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'auth:sanctum'])
            ->group(__DIR__ . '/../../routes/api.php');
    }

    /**
     * Define the "web" routes.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../../routes/web.php');
    }
}
