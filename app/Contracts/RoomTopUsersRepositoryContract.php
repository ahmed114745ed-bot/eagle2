<?php

namespace App\Contracts;

interface RoomTopUsersRepositoryContract
{
    /**
     * Find or create room top user
     */
    public function findOrCreate($roomId, $userId);

    /**
     * Get top user for room
     */
    public function getRoomTopUser($roomId, $with = []);
}
