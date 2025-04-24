<?php

namespace Database\Seeders;

use App\Models\CoreWallets;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoreWalletsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $walletNames = [
            'app_wallet',
            'owner_wallet',
            'game_wallet',
            'lucky_box',
            'agency',
            'host_agency',
            'lucky_gifts',
            'chinese_games',
            'games',
            'shipping_agents',
            'payment_gateways',
            'mall',
            'vip',
            'ads'
        ];

        foreach ($walletNames as $name) {
            CoreWallets::firstOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
