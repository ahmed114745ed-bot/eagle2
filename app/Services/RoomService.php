<?php

namespace App\Services;

use App\Repositories\Room\RoomRepo;
use App\Repositories\Room\RoomRepository;

class RoomService
{
    protected $roomRepo;
    protected $roomRepository;

    public function __construct(RoomRepo $roomRepo,RoomRepository $roomRepository)
    {
        $this->roomRepo = $roomRepo;
        $this->roomRepository = $roomRepository;
    }

    public function getAllRooms($request)
    {
        return $this->roomRepo->all($request);
    }

    public function getRoomsForGame($gameId)
    {
        return $this->roomRepository->getRoomsByGameId($gameId);
    }
}
