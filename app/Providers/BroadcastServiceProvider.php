<?php

namespace App\Providers;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Broadcast;
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
        // Don't load config in boot() for Octane compatibility
        // Instead, load it dynamically per request using middleware
        
        Broadcast::routes(['middleware' => ['auth:sanctum', 'octane.pusher.config2']]);
        require base_path('routes/channels.php');

        // Override Pusher broadcaster to always read from database (Octane-compatible)
        $this->app->extend('broadcast', function (BroadcastManager $manager) {
            $manager->extend('pusher', function ($app) {
                return $this->buildDatabaseDrivenPusherBroadcaster();
            });
            return $manager;
        });
    }

    /**
     * Build Pusher broadcaster that reads config from database on EVERY call
     * This ensures Octane workers always get fresh credentials
     */
    private function buildDatabaseDrivenPusherBroadcaster(): PusherBroadcaster
    {
        // Always read from database, NEVER from config cache
        $config = getPusherConfig();

        if (!$config) {
            // Fallback to env if database read fails
            $config = [
                'app_key' => env('PUSHER_APP_KEY'),
                'app_secret' => env('PUSHER_APP_SECRET'),
                'app_id' => env('PUSHER_APP_ID'),
                'app_cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
            ];
        }

        // Create fresh Pusher instance
        $pusher = new Pusher(
            $config['app_key'],
            $config['app_secret'],
            $config['app_id'],
            [
                'cluster' => $config['app_cluster'] ?? 'mt1',
                'useTLS' => true,
            ]
        );

        return new PusherBroadcaster($pusher);
