<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class RealsPackageServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        if ($this->realsPackageExists()) {
            $this->app->register(\Utd\Reals\RealsServiceProvider::class);
        } else {
            $this->registerNullRealsService();
        }
    }

    public function boot(): void
    {
        //
    }


    protected function realsPackageExists(): bool
    {
        $providerPath = base_path('packages/Utd/Reals/src/RealsServiceProvider.php');
        
        if (!file_exists($providerPath)) {
            return false;
        }

        return class_exists(\Utd\Reals\RealsServiceProvider::class);
    }

    protected function registerNullRealsService(): void
    {
        if (interface_exists(\App\Contracts\RealsContract::class)) {
            $this->app->singleton(
                \App\Contracts\RealsContract::class,
                \App\Services\Null\NullRealsService::class
            );
        }
    }
}
