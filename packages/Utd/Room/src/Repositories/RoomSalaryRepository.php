<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\RoomSalary;

class RoomSalaryRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new RoomSalary());
    }

    public function searchRoomSalary($roomId)
    {
        return $this->model->query()
            ->where('room_id', $roomId)
            ->where('is_paid', 0)
            ->orderByDesc('id')
            ->first();
    }

    public function incrementCutAmount($roomId, $amount)
    {
        $roomSalary = $this->searchRoomSalary($roomId);
        if ($roomSalary) {
            $roomSalary->increment('cut_amount', $amount);
        }
        return true;
    }

    public function getUnpaidSalaries($roomId)
    {
        return $this->model->query()
            ->where('room_id', $roomId)
            ->where('is_paid', 0)
            ->get();
    }

    public function markAsPaid($salaryId)
    {
        return $this->update(['is_paid' => 1], $salaryId);
    }
}
