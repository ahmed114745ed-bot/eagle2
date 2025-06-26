<?php

namespace App\Bd;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class BdServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        // }
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/views', 'bd');
    }

    public function register()
    {
        //
    }
}
