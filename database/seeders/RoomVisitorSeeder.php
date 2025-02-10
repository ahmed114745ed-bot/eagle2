<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomVisitor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomVisitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
/*         for($i=0;$i<10;$i++){

            $room = Room::create([
                'numid' => rand(1000,10000),
                'uid' => User::inRandomOrder()->first()->uuid,
                'room_name' => fake()->name(),

            ]);
            RoomVisitor::create([
                'room_id' => $room->id,
                'user_id' => 828
            ]);
        } */
        $rooms = Room::all();
        foreach($rooms as $room){
            $room->update([
                'uid' => User::inRandomOrder()->first()->id,
            ]);
        }

    }
}
