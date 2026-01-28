<?php

namespace Utd\Room\Services;

use Utd\Room\Repositories\PkRepository;
use Utd\Room\Repositories\RoomRepository;

class PkService
{
    public function __construct(
        protected PkRepository $pkRepository,
        protected RoomRepository $roomRepository
    ) {
    }

    /**
     * Get active PK for room
     */
    public function getActivePk($roomId)
    {
        return $this->pkRepository->getActiveByRoom($roomId);
    }

    /**
     * Get all PKs for room
     */
    public function getRoomPks($roomId)
    {
        return $this->pkRepository->getByRoom($roomId);
    }

    /**
     * Create new PK session
     */
    public function createPk(array $data)
    {
        $room = $this->roomRepository->findById($data['room_id']);
        
        if (!$room) {
            throw new \Exception(__('Room not found'));
        }

        // Check if there's already an active PK
        $activePk = $this->getActivePk($data['room_id']);
        if ($activePk) {
            throw new \Exception(__('There is already an active PK session'));
        }

        $data['status'] = 1;
        $data['end_at'] = now()->addSeconds($data['duration'] ?? config('room.pk.duration', 300));

        return $this->pkRepository->create($data);
    }

    /**
     * End PK session
     */
    public function endPk($pkId)
    {
        return $this->pkRepository->endPk($pkId);
    }

    /**
     * Update PK scores
     */
    public function updateScores($pkId, $team1Score, $team2Score)
    {
        return $this->pkRepository->update([
            't1_score' => $team1Score,
            't2_score' => $team2Score,
        ], $pkId);
    }

    /**
     * Check if room has active PK
     */
    public function hasActivePk($roomId)
    {
        return $this->getActivePk($roomId) !== null;
    }
}
