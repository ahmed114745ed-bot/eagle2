<?php

namespace Utd\Room\Services;

use Exception;
use Utd\Room\Repositories\RoomRepository;
use Utd\Room\Repositories\BanRoomRepository;

class BanRoomService
{
    public function __construct(
        protected RoomRepository $roomRepository,
        protected BanRoomRepository $banRoomRepository
    ) {
    }

    /**
     * Get all bans for a room
     */
    public function getRoomBans($roomId)
    {
        return $this->banRoomRepository->getByRoom($roomId);
    }

    /**
     * Check if user is banned from room
     */
    public function isUserBanned($userId, $roomId)
    {
        return $this->banRoomRepository->isBanned($userId, $roomId);
    }

    /**
     * Ban user from room
     */
    public function banUser($userId, $roomId, $staffId = null, $expireAt = null)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        // Check if already banned
        if ($this->isUserBanned($userId, $roomId)) {
            throw new Exception(__('User is already banned from this room'));
        }

        return $this->banRoomRepository->banUser($userId, $roomId, $staffId, $expireAt);
    }

    /**
     * Unban user from room
     */
    public function unbanUser($userId, $roomId)
    {
        return $this->banRoomRepository->unbanUser($userId, $roomId);
    }

    /**
     * Ban user permanently
     */
    public function banPermanently($userId, $roomId, $staffId = null)
    {
        return $this->banUser($userId, $roomId, $staffId, null);
    }

    /**
     * Ban user temporarily
     */
    public function banTemporarily($userId, $roomId, $minutes, $staffId = null)
    {
        $expireAt = now()->addMinutes($minutes);
        
        return $this->banUser($userId, $roomId, $staffId, $expireAt);
    }
}
