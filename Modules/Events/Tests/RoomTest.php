<?php

namespace Modules\Events\Tests;

use Utd\Room\Entities\Background;
use Utd\Room\Entities\Room;
use Tests\TestCase;

class RoomTest extends TestCase
{

    public function testGetFinalRoomImageAttribute()
    {

        $room = Room::first();

        $model = Background::first();
        echo 'this is background ' . $model->img . PHP_EOL;
        $room->room_background = null;
        $room->save();

        echo($room->final_room_image);

    }
}
