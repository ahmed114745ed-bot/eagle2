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
     * ⭐ NO singleton - extend() called on EVERY app instance
     * Octane flush ensures BroadcastManager is destroyed after each request
     */
    private function registerDatabaseDrivenBroadcaster()
    {
        // ⭐ Direct extend - NO singleton to avoid caching in Octane
        $this->app->make(BroadcastManager::class)->extend('pusher', function ($app, $config) {
            // ⭐ Return NEW broadcaster instance - reads fresh from DB every time
            // Constructor calls getPusherConfig() which reads directly from database
            try {
                Log::debug('BroadcastServiceProvider.creating_database_driven_broadcaster', [
                    'pid' => getmypid(),
                    'timestamp' => now()->toDateTimeString(),
                ]);
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