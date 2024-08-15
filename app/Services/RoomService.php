<?php

namespace App\Services;

use App\Repositories\Room\RoomRepo;

class RoomService
{
    protected $roomRepo;

    public function __construct(RoomRepo $roomRepo)
    {
        $this->roomRepo = $roomRepo;
    }

    public function getAllRooms($request)
    {
        return $this->roomRepo->all($request);
    }
}
