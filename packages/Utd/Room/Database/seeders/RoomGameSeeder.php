<?php

namespace Utd\Room\Database\Seeders;

use Utd\Room\Entities\RoomGame;
use Illuminate\Database\Seeder;

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
            'type' => 'two_player'
        ]);
        RoomGame::create([
            'name' => 'dice',
            'type' => 'two_player'
        ]);
        RoomGame::create([
            'name' => 'spin',
            'type' => 'multi_player'
        ]);
    }
}
