<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Contracts\RoomGameContract;

class JoyGameTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {

        $user = User::where('id',525)->first();

        app(RoomGameContract::class)->updateRoomCoins($user , (-100));
    }
}
