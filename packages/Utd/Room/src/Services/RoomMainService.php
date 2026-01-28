<?php

namespace Utd\Room\Services;

use Exception;
use Utd\Room\Repositories\RoomRepo;
use Utd\Room\Repositories\RoomRepository;

class RoomMainService
{
    protected $roomRepo;
    protected $roomRepository;

    public function __construct(
        RoomRepo $roomRepo,
        RoomRepository $roomRepository,
    ) {
        $this->roomRepo = $roomRepo;
        $this->roomRepository = $roomRepository;
    }

    public function getAllRooms($request)
    {
        return $this->roomRepo->all($request);
    }

    public function roomDetails($userId)
    {
        $room = $this->roomRepository->findRoomUser($userId);
        if (!$room) throw new Exception('This user don\'t have room');
        return $room;
    }
}
