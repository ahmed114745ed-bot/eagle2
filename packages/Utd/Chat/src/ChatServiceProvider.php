<?php

namespace Utd\Chat;

use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'chat');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/utd.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/dashboard.php');

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'chat');

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('chat.php'),
        ], 'chat-config');

        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/chat'),
        ], 'chat-views');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
