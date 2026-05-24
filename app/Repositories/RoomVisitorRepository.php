<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\RoomVisitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoomVisitorRepository
{
    /**
     * Add a visitor to a room with dual-write
     *
     * @param int $roomId
     * @param int $userId
     * @return bool
     */
    public function addVisitor(int $roomId, int $userId): bool
    {
        return DB::transaction(function () use ($roomId, $userId) {
            // Write to new table
            RoomVisitor::firstOrCreate([
                'room_id' => $roomId,
                'user_id' => $userId
            ], [
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Dual-write: update legacy column
            $room = Room::find($roomId);
            if ($room) {
                $visitors = array_filter(explode(',', $room->room_visitor ?? ''));

                if (!in_array($userId, $visitors)) {
                    $visitors[] = $userId;
                    $room->room_visitor = implode(',', $visitors);
                    $room->save();
                }
            }

            return true;
        });
    }

    /**
     * Remove a visitor from a room with dual-write
     *
     * @param int $roomId
     * @param int $userId
     * @return bool
     */
    public function removeVisitor(int $roomId, int $userId): bool
    {
        return DB::transaction(function () use ($roomId, $userId) {
            // Delete from new table
            RoomVisitor::where('room_id', $roomId)
                ->where('user_id', $userId)
                ->delete();

            // Dual-write: update legacy column
            $room = Room::find($roomId);
            if ($room) {
                $visitors = array_filter(explode(',', $room->room_visitor ?? ''));
                $visitors = array_values(array_diff($visitors, [$userId]));
                $room->room_visitor = implode(',', $visitors);
                $room->save();
            }

            return true;
        });
    }

    /**
     * Check if a user is a visitor in a room
     *
     * @param int $roomId
     * @param int $userId
     * @return bool
     */
    public function isVisitor(int $roomId, int $userId): bool
    {
        return RoomVisitor::where('room_id', $roomId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get all visitor IDs for a room
     *
     * @param int $roomId
     * @return Collection
     */
    public function getVisitorIds(int $roomId): Collection
    {
        return RoomVisitor::where('room_id', $roomId)
            ->pluck('user_id');
    }

    /**
     * Get visitor count for a room
     *
     * @param int $roomId
     * @return int
     */
    public function getVisitorCount(int $roomId): int
    {
        return RoomVisitor::where('room_id', $roomId)->count();
    }

    /**
     * Get visitors with user details
     *
     * @param int $roomId
     * @return Collection
     */
    public function getVisitorsWithDetails(int $roomId): Collection
    {
        return RoomVisitor::where('room_id', $roomId)
            ->with('user:id,name,avatar')
            ->get();
    }

    /**
     * Clear all visitors from a room (for room reset)
     *
     * @param int $roomId
     * @return bool
     */
    public function clearAllVisitors(int $roomId): bool
    {
        return DB::transaction(function () use ($roomId) {
            // Delete from new table
            RoomVisitor::where('room_id', $roomId)->delete();

            // Dual-write: clear legacy column
            $room = Room::find($roomId);
            if ($room) {
                $room->room_visitor = '';
                $room->save();
            }

            return true;
        });
    }

    /**
     * Sync visitors based on event (login/logout)
     * This is the main method used by webhooks
     *
     * @param int $roomId
     * @param int $userId
     * @param string $event 'room_login' or 'room_logout'
     * @return bool
     */
    public function syncVisitorByEvent(int $roomId, int $userId, string $event): bool
    {
        if ($event === 'room_login') {
            return $this->addVisitor($roomId, $userId);
        } elseif ($event === 'room_logout') {
            return $this->removeVisitor($roomId, $userId);
        }

        return false;
    }

    /**
     * Get recently joined visitors (last N minutes)
     *
     * @param int $roomId
     * @param int $minutes
     * @return Collection
     */
    public function getRecentVisitors(int $roomId, int $minutes = 5): Collection
    {
        return RoomVisitor::where('room_id', $roomId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->with('user:id,name,avatar')
            ->get();
    }
}
