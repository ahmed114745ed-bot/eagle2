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
     * ⭐ Use afterResolving to register broadcaster when BroadcastManager is ready
     *
     * @return void
     */
    public function register()
    {
     
        $this->app->afterResolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('pusher', function ($app, $config) {
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
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Don't load config in boot() for Octane compatibility
        // Instead, load it dynamically per request using middleware
        
        // Broadcast::routes(['middleware' => ['auth:sanctum', 'octane.pusher.config']]);
        // require base_path('routes/channels.php');
    
        $config = getPusherConfig();

        if ($config) {
            Config::set('broadcasting.connections.pusher.key', @$config['app_key']);
            Config::set('broadcasting.connections.pusher.secret', @$config['app_secret']);
            Config::set('broadcasting.connections.pusher.app_id', @$config['app_id']);
            Config::set('broadcasting.connections.pusher.options.cluster', @$config['app_cluster']);
        }

        Broadcast::routes(["middleware" => "auth:sanctum"]);

        require base_path('routes/channels.php');
    }
}