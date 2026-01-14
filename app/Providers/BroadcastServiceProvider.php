<?php

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Pusher\Pusher;

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
        
        // ⭐ CRITICAL: Override broadcaster creation to ALWAYS read fresh from DB
        // This ensures Octane workers never use stale cached Pusher credentials
        $this->overridePusherBroadcaster();
    }

    /**
     * Override the Pusher broadcaster to read fresh from database every time
     * No caching allowed - config must be always up-to-date
     */
    private function overridePusherBroadcaster()
    {
        $this->app->make(BroadcastManager::class)->extend('pusher', function ($app, $config) {
            // ALWAYS read fresh from database - bypass all caches
            $dbConfig = getPusherConfig();
            
            // Fallback to environment if database returns empty
            if (empty($dbConfig['app_id']) || empty($dbConfig['app_key'])) {
                $dbConfig = [
                    'app_id' => env('PUSHER_APP_ID'),
                    'app_key' => env('PUSHER_APP_KEY'),
                    'app_secret' => env('PUSHER_APP_SECRET'),
                    'app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                ];
                
                Log::warning('broadcaster.pusher.fallback_to_env', [
                    'reason' => 'Database config empty',
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }
            
            // Create fresh Pusher instance with DB config
            $pusher = new Pusher(
                $dbConfig['app_key'],
                $dbConfig['app_secret'],
                $dbConfig['app_id'],
                [
                    'cluster' => $dbConfig['app_cluster'] ?? 'mt1',
                    'useTLS' => true,
                ]
            );
            
            // Return broadcaster instance
            return new PusherBroadcaster($pusher);
        });
    }
}