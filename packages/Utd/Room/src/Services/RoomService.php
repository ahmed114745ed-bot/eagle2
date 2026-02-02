<?php

namespace Utd\Room\Services;

use Exception;
use App\Models\User;
use Utd\Room\Entities\Room;
use App\Contracts\RoomServiceContract;
use Utd\Room\Repositories\RoomRepository;
use Utd\Room\Repositories\RoomVisitorRepository;
use Utd\Room\Repositories\RoomMicrophoneRepository;

class RoomService implements RoomServiceContract
{
    public function __construct(
        protected RoomRepository $roomRepository,
        protected RoomVisitorRepository $visitorRepository,
        protected RoomMicrophoneRepository $microphoneRepository
    ) {
    }

    /**
     * Get all rooms with filters
     */
    public function getAllRooms($request)
    {
        return $this->roomRepository->getAll();
    }

    /**
     * Find room by user ID
     */
    public function findRoomByUser($userId)
    {
        return $this->roomRepository->findRoomUser($userId);
    }

    /**
     * Find room by ID
     */
    public function findRoom($id)
    {
        return $this->roomRepository->findById($id);
    }

    /**
     * Find room by user and type
     */
    public function findRoomByUserAndType($userId, $type)
    {
        return $this->roomRepository->findRoomUserByType($userId, $type);
    }

    /**
     * Create a new room
     */
    public function createRoom(array $data, User $user): Room
    {
        $data['uid'] = $user->id;
        
        return $this->roomRepository->create($data);
    }

    /**
     * Update room
     */
    public function updateRoom($roomId, array $data)
    {
        return $this->roomRepository->update($data, $roomId);
    }

    /**
     * Get room details with all relationships
     */
    public function getRoomDetails($roomId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        return $room->load([
            'owner',
            'roomCategory',
            'microphones.user',
            'roomVisitors.user',
        ]);
    }

    /**
     * Get room admins
     */
    public function getRoomAdmins($roomId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        $adminIds = array_filter(explode(',', $room->room_admin ?? ''));
        
        if (empty($adminIds)) {
            return collect();
        }

        return User::whereIn('id', $adminIds)->get();
    }

    /**
     * Update room status
     */
    public function updateRoomStatus($userId, bool $isAvailable)
    {
        return $this->roomRepository->updateRoomStatus($userId, $isAvailable);
    }

    /**
     * Get rooms by game ID
     */
    public function getRoomsByGame($gameId)
    {
        return $this->roomRepository->getRoomsByGameId($gameId);
    }

    /**
     * Disable/enable writing in room
     */
    public function toggleWriting($roomId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        $room->writing_disabled = !$room->writing_disabled;
        $room->save();

        return $room;
    }

    /**
     * Change room password
     */
    public function changePassword($roomId, ?string $password = null)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        $room->room_pass = $password ?? '';
        $room->save();

        return $room;
    }

    /**
     * Check if user is room owner or admin
     */
    public function isOwnerOrAdmin(User $user, $roomId): bool
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            return false;
        }

        if ($user->id === $room->uid) {
            return true;
        }

        $adminIds = array_filter(explode(',', $room->room_admin ?? ''));
        
        return in_array($user->id, $adminIds);
    }

    /**
     * Add admin to room
     */
    public function addAdmin($roomId, $userId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        $adminIds = array_filter(explode(',', $room->room_admin ?? ''));
        
        if (!in_array($userId, $adminIds)) {
            $adminIds[] = $userId;
            $room->room_admin = implode(',', $adminIds);
            $room->save();
        }

        return $room;
    }

    /**
     * Remove admin from room
     */
    public function removeAdmin($roomId, $userId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new Exception(__('Room not found'));
        }

        $adminIds = array_filter(explode(',', $room->room_admin ?? ''));
        $adminIds = array_diff($adminIds, [$userId]);
        
        $room->room_admin = implode(',', $adminIds);
        $room->save();

        return $room;
    }

    /**
     * Get room background image
     */
    public function getRoomBackground(?Room $room): string
    {
        if ($room == null) return '';
        return $room->final_room_image ?? '';
    }
}
