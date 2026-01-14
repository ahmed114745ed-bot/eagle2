<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Octane\Events\TickReceived;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TickReceived::class => [
            \App\Listeners\OctaneRefreshCacheListener::class,
            // OctaneBroadcasterRefreshListener moved to config/octane.php
        ],
        \App\Events\PusherConfigUpdated::class => [
            \App\Listeners\UpdateBroadcasterConfig::class,
        ],
        // ⭐ Queue: Refresh Pusher config before each job processes
        // This ensures broadcast jobs use fresh DB credentials
        JobProcessing::class => [
            \App\Listeners\RefreshPusherConfigBeforeJob::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // ⭐ Explicitly register JobProcessing listener
        // This ensures it works even if autodiscovery doesn't pick it up
        Event::listen(
            JobProcessing::class,
            [\App\Listeners\RefreshPusherConfigBeforeJob::class, 'handle']
        );
    }
}
