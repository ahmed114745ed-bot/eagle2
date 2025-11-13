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

        try {
            $config = getPusherConfig();

            if (
                $config &&
                !empty($config['app_key']) &&
                !empty($config['app_secret']) &&
                !empty($config['app_id'])
            ) {
                Config::set('broadcasting.connections.pusher.key', $config['app_key']);
                Config::set('broadcasting.connections.pusher.secret', $config['app_secret']);
                Config::set('broadcasting.connections.pusher.app_id', $config['app_id']);
                Config::set('broadcasting.connections.pusher.options.cluster', $config['app_cluster'] ?? 'mt1');
            } else {
                Log::warning('⚠️ Pusher configuration missing or invalid in database.');
            }

            Broadcast::routes(['middleware' => 'auth:sanctum']);
            require base_path('routes/channels.php');
        } catch (\Throwable $e) {
            Log::error('❌ BroadcastServiceProvider error: ' . $e->getMessage());
        }
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
