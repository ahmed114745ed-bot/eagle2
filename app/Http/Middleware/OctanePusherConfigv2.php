<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OctanePusherConfigv2
{
    public function handle(Request $request, Closure $next)
    {
        $config = getPusherConfig(); 

        if ($config) {
            Config::set('broadcasting.connections.pusher.key', $config['app_key'] ?? null);
            Config::set('broadcasting.connections.pusher.secret', $config['app_secret'] ?? null);
            Config::set('broadcasting.connections.pusher.app_id', $config['app_id'] ?? null);
            Config::set('broadcasting.connections.pusher.options.cluster', $config['app_cluster'] ?? null);
        }

        return $next($request);
    }
}
