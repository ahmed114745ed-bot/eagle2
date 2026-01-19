<?php

namespace Utd\Moments\Providers;

use App\Contracts\MomentContract;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Utd\Moments\Http\Middleware\CheckAllowedMoment;
use Utd\Moments\Http\Services\MomentService;

class MomentServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $router->aliasMiddleware('moment.allowed',CheckAllowedMoment::class);
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'Moments');
        $this->loadTranslationsFrom(__DIR__ . '/../../Resources/lang', 'Moments');
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/migrations');
        $this->publishes([__DIR__ . '/../../Config/config.php' => config_path('Moments.php'),], 'Moments-config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MomentContract::class, MomentService::class);
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(__DIR__ . '/../../Config/config.php', 'Moments');
    }
}
