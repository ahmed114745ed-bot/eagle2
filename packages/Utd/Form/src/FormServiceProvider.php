<?php

namespace Utd\Form;

use Illuminate\Support\ServiceProvider;
use Utd\Form\Providers\RouteServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'form');
        $this->app->register(RouteServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'form');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'form');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'Form');

        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/modules/form'),
        ], ['views', 'form-module-views']);

        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('form.php'),
        ], 'config');
    }
}
