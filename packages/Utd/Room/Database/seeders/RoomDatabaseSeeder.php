<?php

namespace Utd\Room\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoomDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Model::unguard();

         $this->call([
             MenuRoomSeeder::class,
             RoomGameSeeder::class
         ]);
    }
}
