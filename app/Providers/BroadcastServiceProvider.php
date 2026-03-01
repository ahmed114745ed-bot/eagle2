<?php

namespace App\Providers;

use App\Broadcasting\DatabaseDrivenPusherBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Broadcasting\Broadcaster as BroadcasterContract;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * ⭐ Register a singleton DatabaseDrivenPusherBroadcaster so the SAME instance
     * is used for both channel registration AND auth. The extend('pusher') callback
     * was creating a NEW instance each time, losing all registered channels.
     *
     * @return void
     */
    public function register()
    {
        // Create a singleton of our custom broadcaster
        $this->app->singleton(DatabaseDrivenPusherBroadcaster::class, function ($app) {
            try {
                return new DatabaseDrivenPusherBroadcaster();
            } catch (\Throwable $e) {
                Log::error('BroadcastServiceProvider.broadcaster_creation_failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
        });

        // After BroadcastManager is resolved, extend 'pusher' to return our singleton
        $this->app->afterResolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('pusher', function ($app, $config) {
                return $app->make(DatabaseDrivenPusherBroadcaster::class);
            });
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes(['middleware' => ['auth:sanctum', 'octane.pusher.config2']]);
        require base_path('routes/channels.php');
    }
}
