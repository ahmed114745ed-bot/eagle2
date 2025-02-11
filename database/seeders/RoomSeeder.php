<?php

namespace Database\Seeders;

use App\Models\Gift;
use App\Models\GiftLog;
use App\Models\Pk;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Room::where('uid', 1)->delete();

        $room = Room::create([
            'uid' => 1,
            'room_name' => fake()->name(),
            'numid' => rand(10000,20000)
        ]);

        $pk = Pk::create([
            'room_id' => $room->id,
            'status' => 1,
            'end_at' => now()->addWeek(), // Example: ends in 2 hours
            'start_at' => now(), // Example: ends in 2 hours
        ]);

        $giftId1 = Gift::inRandomOrder()->first()->id;
        $giftId2 = Gift::inRandomOrder()->first()->id;
        $senderId = User::inRandomOrder()->first()->id;
        $receiverId = User::inRandomOrder()->first()->id;
        $gifts = [
            ['giftId' => $giftId1, 'roomowner_id' => 1, 'giftPrice' => 100, 'sender_id' => $senderId, 'receiver_id' => $receiverId, 'giftNum' => 2, 'giftName' => 'Gold Coin'],
            ['giftId' => $giftId2,'roomowner_id' => 1, 'giftPrice' => 200, 'sender_id' => $senderId, 'receiver_id' => $receiverId, 'giftNum' => 3, 'giftName' => 'Silver Coin'],
        ];

        foreach ($gifts as $giftData) {
            GiftLog::create($giftData);
        }

        for($i=0;$i<10;$i++){

            $room = Room::create([
                'uid' => 828,
                'room_name' => fake()->name(),
                'numid' => rand(10000,20000)
            ]);
            $team1 = User::inRandomOrder()->take(4)->get();
            $team2 = User::inRandomOrder()->take(4)->get();
            Pk::create([
                'room_id' => $room->id,
                'team_1' => $team1[0]->id . ',' . $team1[1]->id . ',' .$team1[2]->id . ',' . $team1[3]->id,
                'team_2' => $team2[0]->id . ',' . $team2[1]->id . ',' .$team2[2]->id . ',' . $team2[3]->id,
                't1_score' => rand(10,100),
                't2_score' => rand(10,100),
                'winner' => 1,
                'start_at' => date('Y-m-d'),
                'end_at' => date('Y-m-d')
            ]);
        }
    }
}
