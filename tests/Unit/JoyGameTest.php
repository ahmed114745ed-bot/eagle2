<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Room;

use App\Models\User;
use App\Http\Services\RoomGameServices;

class JoyGameTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {

        $user = User::where('id',525)->first();

        (new RoomGameServices())->updateRoomCoins($user , (-100));
    }
}
