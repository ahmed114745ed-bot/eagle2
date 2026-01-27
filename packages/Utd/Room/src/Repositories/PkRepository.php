<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\Pk;

class PkRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Pk());
    }

    public function getActiveByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)
            ->where('status', 1)
            ->where('end_at', '>=', now())
            ->orderByDesc('id')
            ->first();
    }

    public function getByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)
            ->orderByDesc('id')
            ->get();
    }

    public function endPk($pkId)
    {
        return $this->update(['status' => 0], $pkId);
    }
}
