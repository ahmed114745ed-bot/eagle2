<?php

namespace Utd\RoomBoom\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class RoomBoomDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $this->call([
            MenuRoomBoomSeeder::class,
        ]);
    }
}
