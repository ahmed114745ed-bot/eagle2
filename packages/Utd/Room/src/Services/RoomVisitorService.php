<?php

namespace Utd\Room\Services;

use Exception;
use Utd\Room\Repositories\RoomRepository;
use Utd\Room\Repositories\RoomVisitorRepository;
use Utd\Room\Repositories\EnteredRoomRepository;

class RoomVisitorService
{
    public function __construct(
        protected RoomRepository $roomRepository,
        protected RoomVisitorRepository $visitorRepository,
        protected EnteredRoomRepository $enteredRoomRepository
    ) {
    }

    /**
     * Get visitors for a room
     */
    public function getVisitors($roomId)
    {
        return $this->visitorRepository->getByRoom($roomId);
    }

    /**
     * Get rooms visited by user
     */
    public function getUserVisitedRooms($userId)
    {
        return $this->visitorRepository->getByUser($userId);
    }

    /**
     * Add visitor to room
     */
    public function addVisitor($userId, $roomId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        // Record entry
        $this->enteredRoomRepository->recordEntry($userId, $roomId);
        
        // Add as visitor
        return $this->visitorRepository->addVisitor($userId, $roomId);
    }

    /**
     * Remove visitor from room
     */
    public function removeVisitor($userId, $roomId)
    {
        return $this->visitorRepository->removeVisitor($userId, $roomId);
    }

    /**
     * Get visitor count for room
     */
    public function getVisitorCount($roomId)
    {
        return $this->visitorRepository->getByRoom($roomId)->count();
    }

    /**
     * Check if user is in room
     */
    public function isUserInRoom($userId, $roomId)
    {
        $visitors = $this->visitorRepository->getByRoom($roomId);
        
        return $visitors->contains('user_id', $userId);
    }

    /**
     * Get recent entered rooms for user
     */
    public function getRecentRooms($userId, $limit = 10)
    {
        return $this->enteredRoomRepository->getRecentByUser($userId, $limit);
    }
}
