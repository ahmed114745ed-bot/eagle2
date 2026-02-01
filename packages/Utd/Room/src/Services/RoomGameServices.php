<?php

namespace Utd\Room\Services;

use Utd\Room\Entities\Room;
use Utd\Room\Entities\RoomSalary;
use Utd\Room\Entities\RoomTarget;
use App\Models\User;

class RoomGameServices
{
    public function updateRoomCoins(User $user, $coins)
    {
        if ($user->now_room_uid != 0 && $user->now_room_uid != null) {
            $room = $user->room;
            if ($room != null) {
                $room->total_game_coins += $coins;
                $room->save();
            }
        }
    }

    public function CalculateRoomSalaries(Room $room)
    {
        $user = $room->owner;
        if ($user->type_user == 1) {
            $total_exp = $room->total_game_coins;
            $target = RoomTarget::where("coins", "<=", $total_exp)->orderBy('coins', 'desc')->first();
            if ($target) {
                $diamond = '"' . $room->total_game_coins . '/' . $target->coins . '"';
                RoomSalary::updateOrCreate([
                    'room_id'   => $room->id,
                    'month'     => date("m"),
                    'year'      => date("Y"),
                ], [
                    "salary" => $target->usd,
                    "diamond" => $diamond,
                ]);
            }
        }
    }
}
