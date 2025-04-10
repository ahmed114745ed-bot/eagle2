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
        $walletNames = ['app_wallet', 'owner_wallet', 'game_wallet', 'lucky_box'];

        foreach ($walletNames as $name) {
            CoreWallets::firstOrCreate(
                ['name' => $name], 
                ['name' => $name]  
            );
        }
    }
}
