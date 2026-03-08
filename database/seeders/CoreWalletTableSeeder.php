<?php

namespace Database\Seeders;

use Utd\UsersWallet\Entities\CoreWallet;
use Illuminate\Database\Seeder;

class CoreWalletTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CoreWallet::create([
            'name' => 'app_wallet',
            'coins'=> 0
        ]);

        CoreWallet::create([
            'name' => 'owner_wallet',
            'coins'=> 0
        ]);
    }
}
