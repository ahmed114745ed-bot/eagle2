<?php

namespace Utd\Moments\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Moments\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../../Routes/api.php');

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../../Routes/web.php');

            Route::prefix('api/Utd')
                ->middleware(['api', 'localization',])
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../../Routes/utd.php');
        });
    }
}
