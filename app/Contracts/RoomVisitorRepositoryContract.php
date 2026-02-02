<?php

namespace App\Contracts;

interface RoomVisitorRepositoryContract
{
    /**
     * Get visitors by user ID
     */
    public function getByUser($id);

    /**
     * Get visitors by room ID
     */
    public function getByRoom($roomId);

    /**
     * Add visitor to room
     */
    public function addVisitor($userId, $roomId);

    /**
     * Remove visitor from room
     */
    public function removeVisitor($userId, $roomId);
}
