<?php

namespace App\Tik\Repositories;

use App\Models\EnteredRoom;
use App\Models\Room;
use App\Models\RoomPrivateMessages;

class RoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Room());
    }


    // public function create($request, $userId)
    // {
    //     $room = $this->model->create(array_merge($request->all(), ['uid' => $userId]));
    //     if ($request->hasFile('room_cover')) {
    //         $room->room_cover = Common::upload('rooms', $request->file('room_cover'));
    //         $room->save();
    //     }

    //     return $room;
    // }

    public function findRoomUser($userId)
    {
        return $this->model->withoutAppends()->where('uid', $userId)->first();
    }

    public function updateRoom($room)
    {
        $room->update();
    }

    public function createPrivetMessage($fromUserId, $toUserId, $message, $price)
    {
        return  RoomPrivateMessages::query()->create([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'message' => $message,
            'price' => $price
        ]);
    }


    public function findRoom($roomId)
    {
        return $this->model->find($roomId);
    }


    public function getRooms($ids)
    {
        return $this->model->whereIn('uid', $ids)->where(function ($q) {
            $q->where('count_room_socket', '!=', 0);
        })->orderBy('hot', 'desc')->get();
    }

    public function updateMicRoom($room, $mic)
    {
        $room->microphone = $mic;
        $this->updateRoomUser($room);
        return true;
    }

    public function updateRoomStatus($userId,$isAvailable)
    {
        return $this->model->query()->where('uid', $userId)->update(['room_status' => $isAvailable ? 2 : 1]);
    }

    public function updateRoomUser($room)
    {
        $room->save();
        return true;
    }

    public function all ( $req )
    {
        $user = $req->user();
        $result = $this->model->with([
            'boxUse' => fn($q) => $q->where('not_used_num', '>=', 1),
            'backgroundImage',
            'lastPk'
        ])
        ->whereHas('owner')
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->where('count_room_socket', '!=', 0)
                  ->where('top_room', 1);
            })->orWhere(function ($q) {
                $q->where('count_room_socket', '!=', 0);
            });
        })
        ->where('room_status', 1);

        // Filter by country if provided
        if (!is_null($req->country_id)) {
            $result->whereHas('owner', function ($q) use ($req) {
                $q->where('country_id', $req->country_id);
            });
        }

        // Apply filters based on 'filter' parameter
        switch ($req->filter) {
            case 'boss':
                $roomIds = EnteredRoom::query()
                    ->where('uid', $user->id)
                    ->orderByDesc('entered_at')
                    ->pluck('rid')
                    ->toArray();
                $result->whereIn('id', $roomIds);
                break;

            case 'trend':
                $result->orderBy('top_room', 'DESC')
                    ->orderByDesc('session');
                break;

            case 'popular':
                $result->orderByDesc('top_room')
                    ->orderByDesc('count_room_socket');
                break;

            case 'festival':
                $result->orderByDesc('top_room')
                    ->orderByDesc('session')
                    ->orderByDesc('count_room_socket');
                break;

            case 'nearby':
                $userLat  = $user->lat;
                $userLong = $user->long;

                // Use lat/long from the related `owner` (User) model
                $result->selectRaw(
                        '*,
                        ( 6371 * acos( cos( radians(?) ) * cos( radians( owner.lat ) ) * cos( radians( owner.long ) - radians(?) ) + sin( radians(?) ) * sin( radians( owner.lat ) ) ) ) AS distance',
                        [$userLat, $userLong, $userLat]
                    )
                    ->join('users as owner', 'rooms.uid', '=', 'owner.id')
                    ->orderBy('distance');
                break;

            default:
                $result->orderByDesc('hour_hot');
                break;
        }
        // Paginate the results with 10 items per page
        return $result->paginate(10);
    }


    public function getRoomsByGameId($gameId = null, array $with = [])
    {
        return $this->model->with($with)
            ->where('game_id', '!=', null)
            ->where('mode', 4)
            ->when(isset($gameId) && $gameId != 'null', function ($query) use ($gameId) {
                $query->where('game_id', $gameId);
            })
            ->get();
    }

}
