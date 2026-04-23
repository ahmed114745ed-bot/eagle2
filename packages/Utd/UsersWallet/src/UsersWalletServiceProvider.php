<?php

namespace Utd\UsersWallet;

use Illuminate\Support\ServiceProvider;
use Utd\UsersWallet\Providers\RouteServiceProvider;
use Utd\UsersWallet\Repositories\Eloquent\WalletRepository;
use Utd\UsersWallet\Repositories\WalletRepositoryInterface;

class UsersWalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'userswallet');
        $this->app->register(RouteServiceProvider::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'userswallet');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'userswallet');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/modules/userswallet'),
        ], ['views', 'userswallet-module-views']);

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('userswallet.php'),
        ], 'config');
    }
}
