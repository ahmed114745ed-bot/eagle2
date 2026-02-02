<?php

namespace App\Contracts;

interface RoomSalaryRepositoryContract
{
    /**
     * Search for unpaid room salary
     */
    public function searchRoomSalary($roomId);

    /**
     * Increment cut amount for room salary
     */
    public function incrementCutAmount($roomId, $amount);

    /**
     * Get all unpaid salaries for room
     */
    public function getUnpaidSalaries($roomId);

    /**
     * Mark salary as paid
     */
    public function markAsPaid($salaryId);

    /**
     * Create new room salary
     */
    public function create(array $data);

    /**
     * Update room salary
     */
    public function update(array $data, $id);

    /**
     * Find by ID
     */
    public function findById($id);
}
