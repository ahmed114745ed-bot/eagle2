<?php

namespace App\Tik\Repositories;

use App\Models\EnteredRoom;
use App\Models\Room;
use App\Models\RoomPrivateMessages;
use Carbon\Carbon;

class RoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Room());
    }


    public function findRoomUser($userId, $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) $model = $model->withoutAppends();
        return $model->where('uid', $userId)->with(['owner', 'roomCategory', 'family'])->first();
    }

    public function findRoomUserEnable($userId)
    {
        return $this->model->withoutAppends()->where('uid', $userId)->with(['owner', 'roomCategory', 'family'])->where('room_status', 1)->first();
    }

    public function findUserRoom($ownerId)
    {
        return $this->model->withoutAppends()->where(['uid' => $ownerId])->selectRaw('id,uid,room_visitor,play_num,hot,room_pass,session,microphone,charizma_status')->first();
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

    public function updateRoomStatus($userId, $isAvailable)
    {
        return $this->model->query()->where('uid', $userId)->update(['room_status' => $isAvailable ? 2 : 1]);
    }

    public function updateRoomUser($room)
    {
        $room->save();
        return true;
    }

    public function all($req)
    {
        $user = $req->user();
        $allRooms = (settings()->get('make_rooms_top') == 1) ?? false;
        $result = $this->model->with([
            'boxUse' => fn($q) => $q->where('not_used_num', '>=', 1),
            'backgroundImage',
            'lastPk',
            'roomVisitorUsers' => fn($q) => $q->limit(5)
        ])
            ->whereHas('owner')
            ->when(!$allRooms, function ($query){
                $query->where(function ($query){
                    $query->where(fn($q) => $q->where('count_room_socket','!=',0)->where('top_room', 1))
                        ->orWhere(fn($q) => $q->where('pin', 1))
                        ->orWhere(fn($q) => $q->where('count_room_socket','!=',0));
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
            case 'recently':
                $result->orderByDesc('top_room')
                    ->orderByDesc('session')
                    ->orderByDesc('count_room_socket');
                break;
            case 'interested':

                $roomTypes = EnteredRoom::query()
                    ->where('uid',  $user->id) 
                    ->where('entered_at', '>=', Carbon::now()->subDay()) 
                    ->with('room') 
                    ->get()
                    ->pluck('room.room_type') 
                    ->unique();

                $result->whereIn("roomTypes",$roomTypes)->orderByDesc('top_room')
                    ->orderByDesc('session')
                    ->orderByDesc('count_room_socket');
                break;
            case 'following':
                $result->whereIn('uid', function ($query) use ($user) {
                    $query->select('followed_id')
                          ->from('follows')
                          ->where('followed_user_id', $user->id);
                })
                    ->orderByDesc('top_room')
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
        return $this->model->withoutAppends()->withCount('roomVisitors')->where('uid', $userId)->first();
    }
}
