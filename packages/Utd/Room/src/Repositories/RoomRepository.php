<?php

namespace Utd\Room\Repositories;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use Utd\Room\Entities\Room;
use Utd\Room\Entities\EnteredRoom;
use Utd\Room\Entities\RoomPrivateMessages;

/**
 * @property Room $model
 */
class RoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Room);
    }

    public function findRoomUser($userId, $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) {
            $model = $model->withoutAppends();
        }
        return $model->where('uid', $userId)
            ->with(['owner', 'roomCategory', 'family'])
            ->first();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function findRoomTypeUser($userId, $type = 'audio', $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) {
            $model = $model->withoutAppends();
        }
        return $model->where('type', $type)
            ->where('uid', $userId)
            ->with(['owner', 'roomCategory', 'family'])
            ->first();
    }

    public function findAudioRoomUser($userId, $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) {
            $model = $model->withoutAppends();
        }
        return $model->where('type', 'audio')
            ->where('uid', $userId)
            ->with(['owner', 'roomCategory', 'family'])
            ->first();
    }

    public function findRoomId($id, $withoutAppends = true)
    {
        $query = $this->model;

        if ($withoutAppends) {
            $query = $query->withoutAppends();
        }
        $query = $query->select(['id', 'uid', 'room_admin']);
        return $query->where('id', $id)->first();
    }

    public function findRoomUserByType($userId, $type, $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) {
            $model = $model->withoutAppends();
        }

        switch ($type) {
            case 'audio':
                return $model->where('uid', $userId)
                    ->where('type', 'audio')
                    ->with(['owner', 'roomCategory', 'family'])
                    ->first();

            case 'live':
                return $model->where('uid', $userId)
                    ->where('type', 'live')
                    ->with(['owner', 'roomCategory', 'family'])
                    ->first();

            default:
                return null;
        }
    }

    public function findRoomAdmins($userId, $withoutAppends = true)
    {
        $query = $this->model;

        if ($withoutAppends) {
            $query = $query->withoutAppends();
        }
        $query = $query->select(['id', 'uid', 'room_admin']);
        return $query->where('uid', $userId)->first();
    }

    public function findRoomUserEnable($userId)
    {
        return $this->model->where('uid', $userId)
            ->with(['owner', 'roomCategory', 'family'])
            ->where('room_status', 1)
            ->first();
    }

    public function findRoomUserEnableAudio($userId)
    {
        return $this->model->where('uid', $userId)
            ->with(['owner', 'roomCategory', 'family'])
            ->where('room_status', 1)
            ->where('type', 'audio')
            ->first();
    }

    public function findUserRoom($ownerId, $selectRow = "*")
    {
        return $this->model->withoutAppends()
            ->where(['uid' => $ownerId])
            ->selectRaw($selectRow)
            ->first();
    }

    public function findTypeUserRoom($ownerId, $type = 'audio', $selectRow = "*")
    {
        return $this->model->withoutAppends()
            ->where(['uid' => $ownerId])
            ->where('type', $type)
            ->selectRaw($selectRow)
            ->first();
    }

    public function findUserRoomById($ownerId, $selectRow = "*")
    {
        return $this->model
            ->withoutAppends()
            ->where(['id' => $ownerId])
            ->selectRaw($selectRow)
            ->first();
    }

    public function updateRoom($room)
    {
        $room->update();
    }

    public function findRoom($roomId)
    {
        return $this->model->find($roomId);
    }

    public function getRooms($ids)
    {
        return $this->model->whereIn('uid', $ids)
            ->where(function ($q) {
                $q->where('count_room_socket', '!=', 0);
            })
            ->orderBy('hot', 'desc')
            ->get();
    }

    public function updateMicRoom($room, $mic)
    {
        $room->microphone = $mic;
        return $room->save();
    }

    public function updateRoomStatus($userId, $isAvailable)
    {
        return $this->model->query()
            ->where('uid', $userId)
            ->update(['room_status' => $isAvailable ? 2 : 1]);
    }

    public function updateRoomUser($room)
    {
        $room->save();
        return true;
    }

    public function getRoomsByGameId($gameId = null, array $with = [])
    {
        return $this->model->with($with)
            ->where('game_id', '!=', null)
            ->where('mode', 4)
            ->when(isset($gameId) && $gameId != 'null', function ($query) use ($gameId) {
                $query->where('game_id', $gameId);
            })->get();
    }

    public function randomOwner()
    {
        return $this->model->query()
            ->where('room_status', 1)
            ->where('uid', '!=', null)
            ->where(function ($q) {
                $q->where('count_room_socket', '!=', 0)->orWhere('is_afk', 1);
            })->pluck('uid')->random();
    }

    public function updateRoomBlack($room, $roomBlack)
    {
        $room->room_black = trim($roomBlack, ',');
        $this->updateRoomUser($room);
    }

    public function roomUsers($userId)
    {
        return $this->model->withoutAppends()
            ->withCount('roomVisitors')
            ->where('uid', $userId)
            ->first();
    }

    public function scopeAudio($query)
    {
        return $query->where('type', 'audio');
    }

    public function scopeLive($query)
    {
        return $query->where('type', 'live');
    }

    protected function getBlockedUserIds()
    {
        return Pack::query()
            ->select('user_id')
            ->where('type', 16)
            ->where('is_used', 1)
            ->where(function ($q) {
                $q->where('expire', 0)
                    ->orWhere('expire', '>=', now()->timestamp);
            })
            ->pluck('user_id');
    }
}
