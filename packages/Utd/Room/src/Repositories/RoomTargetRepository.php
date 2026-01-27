<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\RoomTarget;

class RoomTargetRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new RoomTarget());
    }

    public function getActiveTargets($roomId)
    {
        return $this->model->query()
            ->where('room_id', $roomId)
            ->where('is_active', 1)
            ->get();
    }

    public function findByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)->get();
    }
}
