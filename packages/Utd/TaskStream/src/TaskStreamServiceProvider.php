<?php

namespace Utd\TaskStream;

use App\Contracts\TaskStreamServiceContract;
use Illuminate\Support\ServiceProvider;
use Utd\TaskStream\Services\TaskStreamService;

class TaskStreamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');

        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'taskstream');

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('taskstream.php'),
        ], 'taskstream-config');
    }

    public function register(): void
    {
        $this->app->singleton(TaskStreamServiceContract::class, TaskStreamService::class);
    }
}
