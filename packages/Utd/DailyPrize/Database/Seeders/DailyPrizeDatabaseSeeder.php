<?php

namespace Utd\DailyPrize\Database\Seeders;

use Illuminate\Database\Seeder;

class DailyPrizeDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DailyPrizeMenuSeeder::class,
        ]);
    }
}
