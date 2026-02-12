<?php

namespace Utd\Achievements\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class AchievementDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            MenuAchievementSeeder::class,
        ]);
        //        $this->call(AchievementTableSeeder::class);
        //        $this->call(AchievementLevelTableSeeder::class);
    }
}
