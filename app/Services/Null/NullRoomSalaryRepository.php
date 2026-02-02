<?php

namespace App\Services\Null;

use App\Contracts\RoomSalaryRepositoryContract;

class NullRoomSalaryRepository implements RoomSalaryRepositoryContract
{
    public function searchRoomSalary($roomId)
    {
        return null;
    }

    public function incrementCutAmount($roomId, $amount)
    {
        return true;
    }

    public function getUnpaidSalaries($roomId)
    {
        return collect();
    }

    public function markAsPaid($salaryId)
    {
        return null;
    }

    public function create(array $data)
    {
        return null;
    }

    public function update(array $data, $id)
    {
        return null;
    }

    public function findById($id)
    {
        return null;
    }
}
