<?php

namespace App\Contracts;

use App\Models\User;

interface RoomServiceContract
{
    /**
     * Get all rooms with filters
     */
    public function getAllRooms($request);

    /**
     * Find room by user ID
     */
    public function findRoomByUser($userId);

    /**
     * Find room by ID
     */
    public function findRoom($id);

    /**
     * Find room by user and type
     */
    public function findRoomByUserAndType($userId, $type);

    /**
     * Create a new room
     */
    public function createRoom(array $data, User $user);

    /**
     * Update room
     */
    public function updateRoom($roomId, array $data);

    /**
     * Get room details with all relationships
     */
    public function getRoomDetails($roomId);

    /**
     * Get room admins
     */
    public function getRoomAdmins($roomId);

    /**
     * Update room status
     */
    public function updateRoomStatus($userId, bool $isAvailable);

    /**
     * Get rooms by game ID
     */
    public function getRoomsByGame($gameId);

    /**
     * Disable/enable writing in room
     */
    public function toggleWriting($roomId);

    /**
     * Change room password
     */
    public function changePassword($roomId, ?string $password = null);

    /**
     * Check if user is room owner or admin
     */
    public function isOwnerOrAdmin(User $user, $roomId): bool;

    /**
     * Add admin to room
     */
    public function addAdmin($roomId, $userId);

    /**
     * Remove admin from room
     */
    public function removeAdmin($roomId, $userId);

    /**
     * Get room background image
     */
    public function getRoomBackground($room = null): string;
}
