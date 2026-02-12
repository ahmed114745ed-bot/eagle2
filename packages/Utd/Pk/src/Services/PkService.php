<?php

namespace Utd\Pk\Services;

use App\Contracts\RoomRepositoryContract;
use Carbon\Carbon;
use Exception;
use Utd\Pk\Repositories\PkRepository;

class PkService
{
    public function __construct(
        private readonly PkRepository $pkRepository,
        private readonly RoomRepositoryContract $roomRepository,
    ) {}

    public function create($request, $userId)
    {
        $roomId = $request->room_id;
        $room = $roomId
            ? $this->roomRepository->findById($roomId)
            : $this->roomRepository->findRoomUserEnableAudio($request->owner_id);

        if (! $room) {
            throw new Exception('not found');
        }
        if ($userId !== $room->uid) {
            throw new Exception(__('you don not have permission'));
        }
        if ($room->room_visitor === '') {
            throw new Exception(__('room closed'));
        }
        $ex = $this->pkRepository->getPk($room->id);
        if ($ex) {
            $ex->update(['status' => 0]);
        }
        $data =
            [
                'room_id' => $room->id,
                'status' => 1,
                'mics' => $room->microphone,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMinutes($request->minutes),
            ];
        $pk = $this->pkRepository->create($data);

        return [$pk, $room->id];
    }

    public function closePk($pkId)
    {
        $pk = $this->pkRepository->findByIdAny($pkId);
        if (! $pk) {
            throw new Exception(__('PK not found'));
        }
        if ($pk->status === 0) {
            throw new Exception(__('Already closed'));
        }

        $winner = ($pk->t1_score > $pk->t2_score) ? 1 : (($pk->t2_score > $pk->t1_score) ? 2 : 0);

        $pk->winner = $winner;
        $pk->status = 0;
        $pk->save();

        return $pk;
    }

    public function showPkOrHide($ownerId, $status, bool $isPkCustom = false, $roomId = null)
    {
        $user = request()->user();
        $room = $roomId
            ? $this->roomRepository->findById($roomId)
            : $this->roomRepository->findRoomUserEnableAudio($ownerId);

        if (! $room) {
            throw new Exception('not found');
        }
        if ($user->id !== $room->uid) {
            throw new Exception(__('you don not have permission'));
        }

        if ($room->mode !== 3 && $room->mode !== 9) {
            throw new Exception('Mode Not Compatible');
        }
        $room->enableSaving = false;
        $status === 1 ? $room->update(['is_show_pk' => 1, 'is_pk_custom' => $isPkCustom]) : $room->update(['is_show_pk' => 0, 'is_pk_custom' => 0]);

        return $room;
    }

    public function roomPk($userId, $perPage, $page)
    {
        $pks = $this->pkRepository->roomPks($userId, $perPage, $page);
        if (! $pks) {
            throw new Exception('This user don\'t have room');
        }

        return $pks;
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
