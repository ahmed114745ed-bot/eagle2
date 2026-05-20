<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\RoomAdministrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoomAdministratorRepository
{
    /**
     * Get all administrators for a room
     *
     * @param int $roomId
     * @return Collection
     */
    public function getAdmins(int $roomId): Collection
    {
        return RoomAdministrator::where('room_id', $roomId)
            ->pluck('user_id');
    }

    /**
     * Add an administrator to a room with dual-write
     *
     * @param int $roomId
     * @param int $userId
     * @param int|null $assignedBy
     * @return bool
     */
    public function addAdmin(int $roomId, int $userId, ?int $assignedBy = null): bool
    {
        return DB::transaction(function () use ($roomId, $userId, $assignedBy) {
            // Write to new table
            RoomAdministrator::firstOrCreate([
                'room_id' => $roomId,
                'user_id' => $userId
            ], [
                'assigned_by' => $assignedBy,
                'assigned_at' => now()
            ]);

            // Dual-write: sync legacy column from new table
            $room = Room::find($roomId);
            if ($room) {
                // Get all admins from new table (source of truth)
                $admins = RoomAdministrator::where('room_id', $roomId)
                    ->pluck('user_id')
                    ->toArray();

                // Write to legacy column
                $room->room_admin = implode(',', $admins);
                $room->save();
            }

            return true;
        });
    }

    /**
     * Remove an administrator from a room with dual-write
     *
     * @param int $roomId
     * @param int $userId
     * @return bool
     */
    public function removeAdmin(int $roomId, int $userId): bool
    {
        return DB::transaction(function () use ($roomId, $userId) {
            // Delete from new table
            RoomAdministrator::where('room_id', $roomId)
                ->where('user_id', $userId)
                ->delete();

            // Dual-write: sync legacy column from new table
            $room = Room::find($roomId);
            if ($room) {
                // Get remaining admins from new table (source of truth)
                $admins = RoomAdministrator::where('room_id', $roomId)
                    ->pluck('user_id')
                    ->toArray();

                // Write to legacy column
                $room->room_admin = implode(',', $admins);
                $room->save();
            }

            return true;
        });
    }

    /**
     * Check if a user is an administrator of a room
     *
     * @param int $roomId
     * @param int $userId
     * @return bool
     */
    public function isAdmin(int $roomId, int $userId): bool
    {
        return RoomAdministrator::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get the count of administrators for a room
     *
     * @param int $roomId
     * @return int
     */
    public function getAdminCount(int $roomId): int
    {
        return RoomAdministrator::where('room_id', $roomId)->count();
    }

    /**
     * Get administrators with user details
     *
     * @param int $roomId
     * @return Collection
     */
    public function getAdminsWithDetails(int $roomId): Collection
    {
        return RoomAdministrator::where('room_id', $roomId)
            ->with('user:id,name,avatar')
            ->get();
    }

    /**
     * Check if adding a new admin would exceed the limit
     *
     * @param int $roomId
     * @param int $maxAdmins
     * @return bool
     */
    public function canAddAdmin(int $roomId, int $maxAdmins): bool
    {
        return $this->getAdminCount($roomId) < $maxAdmins;
    }
}
