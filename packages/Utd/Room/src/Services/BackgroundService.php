<?php

namespace Utd\Room\Services;

use Utd\Room\Repositories\BackgroundRepository;
use Utd\Room\Repositories\RoomRepository;

class BackgroundService
{
    public function __construct(
        protected BackgroundRepository $backgroundRepository,
        protected RoomRepository $roomRepository
    ) {
    }

    /**
     * Get all enabled backgrounds
     */
    public function getEnabledBackgrounds()
    {
        return $this->backgroundRepository->getEnabled();
    }

    /**
     * Get default background
     */
    public function getDefaultBackground()
    {
        return $this->backgroundRepository->getDefault();
    }

    /**
     * Set room background
     */
    public function setRoomBackground($roomId, $backgroundId)
    {
        $room = $this->roomRepository->findById($roomId);
        
        if (!$room) {
            throw new \Exception(__('Room not found'));
        }

        $room->room_background = $backgroundId;
        $room->save();

        return $room;
    }

    /**
     * Create new background
     */
    public function createBackground(array $data)
    {
        return $this->backgroundRepository->create($data);
    }

    /**
     * Update background
     */
    public function updateBackground($id, array $data)
    {
        return $this->backgroundRepository->update($data, $id);
    }

    /**
     * Delete background
     */
    public function deleteBackground($id)
    {
        return $this->backgroundRepository->delete($id);
    }

    /**
     * Enable background
     */
    public function enableBackground($id)
    {
        return $this->backgroundRepository->update(['enable' => 1], $id);
    }

    /**
     * Disable background
     */
    public function disableBackground($id)
    {
        return $this->backgroundRepository->update(['enable' => 0], $id);
    }
}
