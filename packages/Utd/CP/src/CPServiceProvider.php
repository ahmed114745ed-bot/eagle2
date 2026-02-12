<?php

namespace Utd\CP;

use App\Contracts\CpRepositoryContract;
use App\Contracts\CpServiceContract;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\CP\Repositories\CpRepository;
use Utd\CP\Services\CpService;

class CPServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'CP';

    protected string $moduleNameLower = 'cp';

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'cp');

        $this->app->singleton(CpServiceContract::class, CpService::class);
        $this->app->singleton(CpRepositoryContract::class, CpRepository::class);
    }

    /**
     * Boot the application events.
     */
    public function boot(Router $router): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerPublishing();
        $this->registerCommands();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__.'/../Routes/api.php');

        // Register web/admin routes
        if (file_exists(__DIR__.'/../Routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        }
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
    }

    /**
     * Register views.
     */
    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath,
        ], ['views', $this->moduleNameLower.'-views']);

        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    /**
     * Register translations.
     */
    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);
        $sourcePath = __DIR__.'/../Resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom($sourcePath, $this->moduleNameLower);
        }
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\WeeklyCpWinnerConsole::class,
            ]);
        }
    }

    /**
     * Register publishing.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config/config.php' => config_path('cp.php'),
            ], 'cp-config');

            $this->publishes([
                __DIR__.'/../Database/migrations' => database_path('migrations'),
            ], 'cp-migrations');
        }
    }
}
