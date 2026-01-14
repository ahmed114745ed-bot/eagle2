<?php

namespace App\Providers;

use App\Broadcasting\DatabaseDrivenPusherBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes(['middleware' => ['auth:sanctum', 'octane.pusher.config2']]);
        require base_path('routes/channels.php');
        
        // ⭐ CRITICAL: Override broadcaster to use custom DatabaseDrivenPusherBroadcaster
        // This ensures Octane workers ALWAYS read fresh from DB
        $this->registerDatabaseDrivenBroadcaster();
    }

    /**
     * Register custom database-driven Pusher broadcaster
     * Creates new instance on every call - no caching
     */
    private function registerDatabaseDrivenBroadcaster()
    {
        $this->app->make(BroadcastManager::class)->extend('pusher', function ($app, $config) {
            // ⭐ Return custom broadcaster that reads from DB in constructor
            // Fresh instance created every time - Octane flush ensures this
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
    }
}