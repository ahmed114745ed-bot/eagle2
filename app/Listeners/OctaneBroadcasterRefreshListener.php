<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;

/**
 * Listener to refresh Pusher broadcaster credentials on every TickReceived event
 * Ensures broadcast events always use fresh database credentials
 */
class OctaneBroadcasterRefreshListener
{
    /**
     * Handle TickReceived events to rebuild broadcaster with fresh credentials
     */
    public function handle($event): void
    {
        try {
            OctaneBroadcasterService::rebuildBroadcaster();
        } catch (\Exception $e) {
            logger('OctaneBroadcasterRefreshListener Error: ' . $e->getMessage());
        }
    }
}
