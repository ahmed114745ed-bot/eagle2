<?php

namespace Modules\Wallet\Database\Seeders;

use Illuminate\Database\Seeder;

class WalletDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DigitalWalletTemplateSeeder::class,
            BankAccountTemplateSeeder::class
        ]);
    }
}
