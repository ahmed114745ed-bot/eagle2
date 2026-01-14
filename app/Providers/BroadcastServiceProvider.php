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
        // ⭐ Register broadcaster extension when BroadcastManager is resolved
        // This ensures proper dependency injection
        $this->app->afterResolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('pusher', function ($app, $config) {
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