<?php

namespace Utd\UsersWallet;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Utd\UsersWallet\Repositories\Eloquent\WalletRepository;
use Utd\UsersWallet\Contracts\WalletRepositoryInterface;
use Utd\UsersWallet\Contracts\WalletServiceInterface;
use Utd\UsersWallet\Services\WalletService;
use Utd\UsersWallet\Console\InstallUsersWalletCommand;
use Utd\UsersWallet\Console\UninstallUsersWalletCommand;

class UsersWalletServiceProvider extends ServiceProvider
{
    /**
     * @var string $namespace
     */
    protected $namespace = 'Utd\UsersWallet\Http\Controllers';

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'UsersWallet';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'userswallet';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerCommands();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Register Configs
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'userswallet');
        $this->mergeConfigFrom(__DIR__ . '/../Config/users_wallet-dependencies.php', 'userswallet-dependencies');

        // Register Repository
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);

        // Register Service
        $this->app->singleton(WalletServiceInterface::class, function ($app) {
            return $app->make(WalletService::class);
        });

        // Register Facade Alias
        $this->app->alias(WalletServiceInterface::class, 'wallet');
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        // Web Routes
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/web.php');

        // API Routes
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/api.php');
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('userswallet.php'),
            __DIR__ . '/../Config/users_wallet-dependencies.php' => config_path('users_wallet-dependencies.php'),
        ], 'userswallet-config');
    }

    /**
     * Register commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallUsersWalletCommand::class,
                UninstallUsersWalletCommand::class,
            ]);
        }
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = __DIR__ . '/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
