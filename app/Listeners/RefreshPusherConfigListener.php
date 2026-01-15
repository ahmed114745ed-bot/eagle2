<?php

namespace App\Listeners;

use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Config;
use Laravel\Octane\Events\RequestReceived;

class RefreshPusherConfigListener
{
 
    public function handle(RequestReceived $event): void
    {
        try {
            $pusherConfig = getPusherConfig();

            if (
                $pusherConfig &&
                !empty($pusherConfig['app_key']) &&
                !empty($pusherConfig['app_secret']) &&
                !empty($pusherConfig['app_id'])
            ) {
                Config::set([
                    'broadcasting.connections.pusher.key' => $pusherConfig['app_key'],
                    'broadcasting.connections.pusher.secret' => $pusherConfig['app_secret'],
                    'broadcasting.connections.pusher.app_id' => $pusherConfig['app_id'],
                    'broadcasting.connections.pusher.options.cluster' => $pusherConfig['app_cluster'] ?? 'mt1',
                ]);

                if (OctaneBroadcasterService::isOctane()) {
                    OctaneBroadcasterService::rebuildBroadcaster();
                }
            }
        } catch (\Throwable $e) {
        }
    }
}

