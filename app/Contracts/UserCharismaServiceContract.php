<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface UserCharismaServiceContract
{
    /**
     * Get room charisma data
     */
    public function roomCharisma($room_id);

    /**
     * Remove user from room when leaving mic
     */
    public function RemoveUserRoomWhenLeaveMic($userId, $roomId);

    /**
     * Add total earned coins for users in room
     */
    public function addTotalEarnedCoinsInUserRoom($room, array $userIds, $earnedCoins = null): false|array;

    /**
     * Add total earned coins for users in room (version 2)
     */
    public function addTotalEarnedCoinsInUserRoom2($room, array $userIds, $earnedCoins = null): false|array;

    /**
     * Get user ID with position from microphones string
     */
    public function getUserIdWithPosition($microphones): Collection;

    /**
     * Get user ID with position from room
     */
    public function getUserIdWithPosition2($room);

    /**
     * Remove all charisma data for a room
     */
    public function removeRoomCharisma(int $roomId);

    /**
     * Reset charisma for specific user in room
     */
    public function resetUserCharisma(int $userId, int $roomId);

    /**
     * Get user reset data from microphones string
     */
    public function getUserResetData($microphones, array $userIds);

    /**
     * Get user reset data from room
     */
    public function getUserResetData2($room, array $userIds);
}
