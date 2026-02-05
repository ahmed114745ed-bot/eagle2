<?php

namespace Utd\Pk;

use App\Contracts\PkRepositoryContract;
use Utd\Pk\Entities\Pk;
use Utd\Pk\Observers\PKObserver;
use Utd\Pk\Repositories\PkRepository;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PkServiceProvider extends ServiceProvider
{
    protected $namespace = 'Utd\\Pk\\Http\\Controllers';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/pk.php', 'pk');

        $this->app->singleton(PkRepositoryContract::class, PkRepository::class);
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerObservers();
    }

    /**
     * Register model observers.
     *
     * @return void
     */
    protected function registerObservers(): void
    {
        Pk::observe(PKObserver::class);
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->group(__DIR__ . '/../Routes/api.php');
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/pk.php' => config_path('pk.php'),
            ], 'pk-config');

            $this->publishes([
                __DIR__ . '/../Database/migrations' => database_path('migrations'),
            ], 'pk-migrations');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }
}
