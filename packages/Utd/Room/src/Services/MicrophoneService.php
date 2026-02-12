<?php

namespace Utd\Room\Services;

use Exception;
use Utd\Room\Repositories\RoomMicrophoneRepository;
use Utd\Room\Repositories\RoomRepository;

class MicrophoneService
{
    public function __construct(
        protected RoomRepository $roomRepository,
        protected RoomMicrophoneRepository $microphoneRepository
    ) {}

    /**
     * Get all microphones for a room
     */
    public function getRoomMicrophones($roomId)
    {
        return $this->microphoneRepository->getByRoom($roomId);
    }

    /**
     * Get microphone at position
     */
    public function getMicrophoneAtPosition($roomId, $position)
    {
        return $this->microphoneRepository->getByPosition($roomId, $position);
    }

    /**
     * Assign user to microphone
     */
    public function assignUserToMic($roomId, $position, $userId)
    {
        $room = $this->roomRepository->findById($roomId);

        if (! $room) {
            throw new Exception(__('Room not found'));
        }

        return $this->microphoneRepository->assignUser($roomId, $position, $userId);
    }

    /**
     * Remove user from microphone
     */
    public function removeUserFromMic($roomId, $position)
    {
        return $this->microphoneRepository->removeUser($roomId, $position);
    }

    /**
     * Update microphone status
     */
    public function updateMicStatus($roomId, $position, $status)
    {
        return $this->microphoneRepository->updateStatus($roomId, $position, $status);
    }

    /**
     * Clear all microphones in room
     */
    public function clearAllMicrophones($roomId)
    {
        return $this->microphoneRepository->clearRoom($roomId);
    }

    /**
     * Initialize microphones for new room
     */
    public function initializeMicrophones($roomId, $count = 8)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->microphoneRepository->create([
                'room_id' => $roomId,
                'position' => $i,
                'user_id' => null,
                'status' => 0,
            ]);
        }

        return $this->getRoomMicrophones($roomId);
    }

    /**
     * Get users on microphones
     */
    public function getUsersOnMic($roomId)
    {
        $microphones = $this->microphoneRepository->getByRoom($roomId);

        return $microphones->filter(fn ($mic) => $mic->user_id !== null);
    }

    /**
     * Check if user is on any microphone
     */
    public function isUserOnMic($roomId, $userId)
    {
        $microphones = $this->microphoneRepository->getByRoom($roomId);

        return $microphones->contains('user_id', $userId);
    }
}
