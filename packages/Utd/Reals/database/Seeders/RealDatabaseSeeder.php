<?php

namespace Utd\Reals\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class RealDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

        $this->call([
            MenuRealSeeder::class,
        ]);
    }
}
