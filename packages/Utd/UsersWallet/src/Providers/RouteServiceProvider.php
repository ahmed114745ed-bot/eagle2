<?php

namespace Utd\UsersWallet\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapUtdRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../../Routes/web.php');
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/../../Routes/api.php');
    }

    protected function mapUtdRoutes(): void
    {
        Route::prefix('api/utd')
            ->middleware(['api', 'localization'])
            ->group(__DIR__ . '/../../Routes/utd.php');
    }
}
