<?php

namespace App\AreaManager;

use Illuminate\Support\ServiceProvider;

class AreaManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/views', 'areaManager');
    }

    public function register()
    {
        //
    }
}
