<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
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
        // Don't load config in boot() for Octane compatibility
        // Instead, load it dynamically per request using middleware
        
        Broadcast::routes(['middleware' => ['auth:sanctum', 'octane.pusher.config2']]);
        require base_path('routes/channels.php');
    
        // $config = getPusherConfig();

        // if ($config) {
        //     Config::set('broadcasting.connections.pusher.key', @$config['app_key']);
        //     Config::set('broadcasting.connections.pusher.secret', @$config['app_secret']);
        //     Config::set('broadcasting.connections.pusher.app_id', @$config['app_id']);
        //     Config::set('broadcasting.connections.pusher.options.cluster', @$config['app_cluster']);
        // }

        // Broadcast::routes(["middleware" => "auth:sanctum"]);

        // require base_path('routes/channels.php');
    }
}
