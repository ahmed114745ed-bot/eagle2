<?php

namespace App\Providers;

use App\Helpers\Common;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Config::set('exp_percentages', $this->getReceivedAndSanderPercentage());

         $config = getPusherConfig();

         if ($config) {
             Config::set('broadcasting.connections.pusher.key', @$config['app_key']);
             Config::set('broadcasting.connections.pusher.secret', @$config['app_secret']);
             Config::set('broadcasting.connections.pusher.app_id', @$config['app_id']);
             Config::set('broadcasting.connections.pusher.options.cluster', @$config['app_cluster']);
         }
    }

    public function getReceivedAndSanderPercentage(): array
    {
        $keys       = [ 'exp_sender_percentage', 'exp_received_percentage', 'exp_cp_percentage'];
        $collection = Common::getConfFromKey($keys);
        $values = [];
        foreach ($keys as $key) {
            $config    = $collection->where('name', $key)->first();
            $values[] = $config? $config->value / 100 : 1;
        }
        unset($collection);
        return $values;
    }
}
