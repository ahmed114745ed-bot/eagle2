<?php

namespace App\Jobs;

use App\Http\Services\RoomGameServices;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class JoyPlayCoinGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $coins;
    public function __construct(User $user, $coins)
    {
        $this->user = $user;
        $this->coins = $coins;
    }

    public function handle()
    {
            (new RoomGameServices())->updateRoomCoins($this->user , $this->coins);
    }


}
