<?php

namespace App\Tik\Services;

use Carbon\Carbon;
use App\Tik\Repositories\PkRepository;
use App\Tik\Repositories\RoomRepository;


class PkService
{
    public function __construct(
        private readonly PkRepository $pkRepository,
        private readonly RoomRepository $roomRepository,
    ) {
    }


    public function create($request, $userId)
    {
        $room =  $this->roomRepository->findRoomUserEnable($request->owner_id);
        if (!$room) throw new \Exception('not found');
        if ($userId != $room->uid  && $room->room_visitor = '')  throw new \Exception('room closed');
        $ex =  $this->pkRepository->getPk($room->id);
        if ($ex) $ex->update(['status' => 0]);
        $data =
            [
                'room_id'  => $room->id,
                'status'   => 1,
                'mics'     => $room->microphone,
                'start_at' => Carbon::now(),
                'end_at'   => Carbon::now()->addMinutes($request->minutes),
            ];
        $pk =   $this->pkRepository->create($data);
        return [$pk, $room->id];
    }


    public function closePk($pkId)
    {
        $pk = $this->pkRepository->findById($pkId);
        if (!$pk) throw new \Exception(__('api_responses.closed'));

        $winner = ($pk->t1_score > $pk->t2_score) ?  1 : (($pk->t2_score > $pk->t1_score) ?  2 :  0);

        $pk->winner = $winner;
        $pk->status = 0;
        $pk->save();
        return $pk;
    }

    public function showPkOrHide($ownerId, $status)
    {
        $room =  $this->roomRepository->findRoomUser($ownerId);
        if (!$room) throw new \Exception('not found');

        $room->enableSaving = false;
        $status == 1 ? $room->update(['is_show_pk' => 1]) : $room->update(['is_show_pk' => 0, 'is_pk_custom' => 0]);
        return $room;
    }
}
