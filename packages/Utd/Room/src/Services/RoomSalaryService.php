<?php

namespace Utd\Room\Services;

use Utd\Room\Repositories\RoomSalaryRepository;
use Utd\Room\Repositories\RoomRepository;

class RoomSalaryService
{
    public function __construct(
        protected RoomSalaryRepository $salaryRepository,
        protected RoomRepository $roomRepository
    ) {
    }

    /**
     * Get unpaid salary for room
     */
    public function getUnpaidSalary($roomId)
    {
        return $this->salaryRepository->searchRoomSalary($roomId);
    }

    /**
     * Get all unpaid salaries for room
     */
    public function getAllUnpaidSalaries($roomId)
    {
        return $this->salaryRepository->getUnpaidSalaries($roomId);
    }

    /**
     * Add salary to room
     */
    public function addSalary($roomId, $amount, $userId = null)
    {
        return $this->salaryRepository->create([
            'room_id' => $roomId,
            'user_id' => $userId,
            'salary' => $amount,
            'cut_amount' => 0,
            'is_paid' => 0,
        ]);
    }

    /**
     * Increment cut amount
     */
    public function incrementCutAmount($roomId, $amount)
    {
        return $this->salaryRepository->incrementCutAmount($roomId, $amount);
    }

    /**
     * Mark salary as paid
     */
    public function markAsPaid($salaryId)
    {
        return $this->salaryRepository->markAsPaid($salaryId);
    }

    /**
     * Calculate total unpaid salary for room
     */
    public function calculateTotalUnpaid($roomId)
    {
        $salaries = $this->salaryRepository->getUnpaidSalaries($roomId);
        
        return $salaries->sum(fn($s) => $s->salary - $s->cut_amount);
    }
}
