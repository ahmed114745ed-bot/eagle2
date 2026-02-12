<?php

namespace Utd\Room\Database\Seeders;

use Illuminate\Database\Seeder;
use Utd\Room\Entities\RoomGame;

class RoomGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RoomGame::create([
            'name' => 'RPS',
            'type' => 'two_player',
        ]);
        RoomGame::create([
            'name' => 'dice',
            'type' => 'two_player',
        ]);
        RoomGame::create([
            'name' => 'spin',
            'type' => 'multi_player',
        ]);
    }
}
