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
     * Register any application services.
     * ⭐ MUST register broadcaster BEFORE boot() to ensure it's available early
     *
     * @return void
     */
    public function register()
    {
        // ⭐ CRITICAL: Register broadcaster in register() NOT boot()
        // This ensures it's available BEFORE any broadcasting happens
        $this->registerDatabaseDrivenBroadcaster();
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

    /**
     * Register custom database-driven Pusher broadcaster
     * Creates new instance on every call - no caching
     */
    private function registerDatabaseDrivenBroadcaster()
    {
        // ⭐ Use singleton to ensure extend() is called once per app instance
        $this->app->singleton('broadcast.pusher.extended', function () {
            $this->app->make(BroadcastManager::class)->extend('pusher', function ($app, $config) {
                // ⭐ Return custom broadcaster that reads from DB in constructor
                // Fresh instance created every time - Octane flush ensures this
                try {
                    Log::debug('BroadcastServiceProvider.creating_database_driven_broadcaster');
                    return new DatabaseDrivenPusherBroadcaster();
                } catch (\Throwable $e) {
                    Log::error('BroadcastServiceProvider.broadcaster_creation_failed', [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                    ]);
                    throw $e;
                }
            });
            return true;
        });
        
        // Resolve immediately to trigger registration
        $this->app->make('broadcast.pusher.extended');
    }
}