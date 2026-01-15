<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OctaneBroadcasterService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class TestPusherAutoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pusher:test-auto-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Pusher auto-update system in Octane';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Pusher Auto-Update System...');
        $this->newLine();

        // 1. Check if Octane is running
        $this->info('1. Checking Octane status...');
        $isOctane = OctaneBroadcasterService::isOctane();
        if ($isOctane) {
            $this->info('   ✅ Octane is running');
        } else {
            $this->warn('   ⚠️  Octane is NOT running (system will work differently)');
        }
        $this->newLine();

        // 2. Get current Pusher config from database
        $this->info('2. Getting Pusher config from database...');
        $dbConfig = getPusherConfig();
        $this->table(
            ['Config', 'Value'],
            [
                ['pusher_app_id', $this->mask($dbConfig['app_id'] ?? '')],
                ['pusher_app_key', $this->mask($dbConfig['app_key'] ?? '')],
                ['pusher_app_secret', $this->mask($dbConfig['app_secret'] ?? '')],
                ['pusher_app_cluster', $dbConfig['app_cluster'] ?? ''],
            ]
        );
        $this->newLine();

        // 3. Get runtime config
        $this->info('3. Getting runtime config...');
        $runtimeConfig = [
            'key' => Config::get('broadcasting.connections.pusher.key'),
            'secret' => Config::get('broadcasting.connections.pusher.secret'),
            'app_id' => Config::get('broadcasting.connections.pusher.app_id'),
            'cluster' => Config::get('broadcasting.connections.pusher.options.cluster'),
        ];
        $this->table(
            ['Config', 'Value'],
            [
                ['runtime_key', $this->mask($runtimeConfig['key'] ?? '')],
                ['runtime_secret', $this->mask($runtimeConfig['secret'] ?? '')],
                ['runtime_app_id', $this->mask($runtimeConfig['app_id'] ?? '')],
                ['runtime_cluster', $runtimeConfig['cluster'] ?? ''],
            ]
        );
        $this->newLine();

        // 4. Check if configs match
        $this->info('4. Comparing database vs runtime...');
        $matches = [
            'key' => ($dbConfig['app_key'] ?? '') === ($runtimeConfig['key'] ?? ''),
            'secret' => ($dbConfig['app_secret'] ?? '') === ($runtimeConfig['secret'] ?? ''),
            'app_id' => ($dbConfig['app_id'] ?? '') === ($runtimeConfig['app_id'] ?? ''),
            'cluster' => ($dbConfig['app_cluster'] ?? '') === ($runtimeConfig['cluster'] ?? ''),
        ];

        foreach ($matches as $field => $match) {
            if ($match) {
                $this->info("   ✅ {$field}: MATCH");
            } else {
                $this->error("   ❌ {$field}: MISMATCH");
            }
        }
        $this->newLine();

        // 5. Check cache flags
        $this->info('5. Checking cache flags...');
        $cacheFlags = [
            'pusher_config_changed' => Cache::has('pusher_config_changed'),
            'octane_broadcaster_rebuilt_at' => Cache::get('octane_broadcaster_rebuilt_at'),
        ];
        $this->table(
            ['Flag', 'Value'],
            [
                ['pusher_config_changed', $cacheFlags['pusher_config_changed'] ? '✅ SET' : '❌ NOT SET'],
                ['octane_broadcaster_rebuilt_at', $cacheFlags['octane_broadcaster_rebuilt_at'] ?? 'Not set'],
            ]
        );
        $this->newLine();

        // 6. Test manual update
        $this->info('6. Testing manual config update...');
        if ($this->confirm('Do you want to trigger a manual update?', false)) {
            try {
                OctaneBroadcasterService::updateRuntimeConfigFromDb();
                OctaneBroadcasterService::rebuildBroadcaster();
                $this->info('   ✅ Manual update completed successfully');
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
            }
        }
        $this->newLine();

        // 7. Summary
        $allMatch = !in_array(false, $matches);
        if ($allMatch) {
            $this->info('🎉 All checks passed! System is working correctly.');
        } else {
            $this->warn('⚠️  Some configs do not match. This might be normal if you just updated them.');
            $this->info('   Wait 1-10 seconds and run this command again to verify auto-update.');
        }

        return 0;
    }

    /**
     * Mask sensitive values
     */
    private function mask(string $value): string
    {
        if (empty($value)) {
            return 'NOT SET';
        }
        
        $len = strlen($value);
        if ($len <= 8) {
            return str_repeat('*', max(0, $len - 2)) . substr($value, -2);
        }
        
        return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
    }
}
