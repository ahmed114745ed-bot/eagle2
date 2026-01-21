<?php

namespace App\Providers;

use App\Listeners\RefreshPusherConfigBeforeJob;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Queue\Events\JobProcessing;
use Laravel\Octane\Events\TickReceived;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
        // Note: JobProcessing listener registered in boot() for reliability
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            try {
                $listener = app(RefreshPusherConfigBeforeJob::class);
                $listener->handle($event);
            } catch (\Throwable $e) {
                Log::error('RefreshPusherConfigBeforeJob failed', [
                    'error' => $e->getMessage(),
                    'job' => $event->job->resolveName() ?? 'unknown',
                ]);
            }
        });
    }
}
