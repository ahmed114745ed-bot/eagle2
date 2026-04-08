<?php

namespace Database\Seeders;


use App\Models\GameProviderSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;


class WebhookGamesSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'provider_code' => 'bytesun',
                'provider_name' => 'Bytesun',
                'webhook_routes' => [
                    'get_unique_id'  => config('app.url') . '/api/baishun/get_unique_id',
                    'get_user_info'  => config('app.url') . '/api/baishun/get-user-info',
                    'get_sstoken'    => config('app.url') . '/api/baishun/get-sstoken',
                    'update_sstoken' => config('app.url') . '/api/baishun/update-sstoken',
                    'change_balance' => config('app.url') . '/api/baishun/change-balance',
                ],
            ],
            [
                'provider_code' => 'quantum_nexus',
                'provider_name' => 'Quantum Nexus',
                'webhook_routes' => [
                    'get_user_info'  => config('app.url') . '/api/leader-cc-game/get-user-info',
                    'make_up_orders' => config('app.url') . '/api/leader-cc-game/make-up-orders',
                    'change_balance' => config('app.url') . '/api/leader-cc-game/change-balance',
                ],
            ],
            [
                'provider_code' => 'utd_games',
                'provider_name' => 'UTD Games',
                'webhook_routes' => [
                    'get_user_info'  => config('app.url') . '/api/utd-game/get-user-info',
                    'change_balance' => config('app.url') . '/api/utd-game/change-balance',
                    'make_up_orders' => config('app.url') . '/api/utd-game/make-up-orders',
                ],
            ],
        ];

        foreach ($providers as $provider) {

            $gameSetting = GameProviderSetting::updateOrCreate(
                ['provider_code' => $provider['provider_code']], // condition
                $provider // values
            );

            // 🔥 Refresh cache
            $cacheKey = 'game_provider_' . $provider['provider_code'];

            Cache::forget($cacheKey);
            Cache::put($cacheKey, $gameSetting);
        }
    }
}
