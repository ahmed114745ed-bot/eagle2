<?php

namespace Utd\UsersWallet\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersWalletDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            DigitalWalletTemplateSeeder::class,
            OtherWalletTemplateSeeder::class,
            BankAccountTemplateSeeder::class
        ]);
    }
}
