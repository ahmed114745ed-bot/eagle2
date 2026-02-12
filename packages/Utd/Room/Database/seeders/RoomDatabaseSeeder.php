<?php

namespace Utd\Room\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class RoomDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $this->call([
            MenuRoomSeeder::class,
            RoomGameSeeder::class,
        ]);
    }
}
