<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OctanePusherConfig
{
    /**
     * Handle an incoming request.
     * Load Pusher config dynamically per request (Octane compatibility)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Load fresh config from database on each request
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
                
                Log::debug('Octane: Pusher config loaded dynamically', [
                    'app_id' => $config['app_id'],
                    'cluster' => $config['app_cluster'] ?? 'mt1'
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('❌ OctanePusherConfig middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
