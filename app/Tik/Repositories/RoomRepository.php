<?php

namespace App\Tik\Repositories;

use App\Models\EnteredRoom;
use App\Models\Pack;
use App\Models\Room;
use App\Models\RoomPrivateMessages;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/** @property Room $model*/
class RoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Room);
    }

    public function findRoomUser($userId, $withoutAppends = true)
    {
        $model = $this->model;
        if ($withoutAppends) $model = $model->withoutAppends();
        return $model->where('uid', $userId)->with(['owner', 'roomCategory', 'family'])->first();
    }

    public function findRoomAdmins($userId, $withoutAppends = true)
    {
        $query = $this->model;

        if ($withoutAppends) {
            $query = $query->withoutAppends();
        }
        $query = $query->select('id', 'uid', 'room_admin');
        $query = $query->with(['family:id,user_id,name,image']);
        return $query->where('uid', $userId)->first();
    }


    public function findRoomUserEnable($userId)
    {
        return $this->model->where('uid', $userId)->with(['owner', 'roomCategory', 'family'])->where('room_status', 1)->first();
    }

    public function findUserRoom($ownerId, $selectRow = "*")
    {
        return $this->model->withoutAppends()->where(['uid' => $ownerId])->selectRaw($selectRow)->first();
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

    public function all($req, $ids = [])
    {
        $roomType = $req->room_type ?? 'audio';
        $user = $req?->user();
        $topRooms = (settings()->get('make_rooms_top') == 1) ?? false;

        $blockedUserIds = Pack::query()
            ->select('user_id')
            ->where('type', 16)
            ->where('is_used', 1)
            ->where(function ($q) {
                $q->where('expire', 0)
                    ->orWhere('expire', '>=', now()->timestamp);
            })
            ->pluck('user_id');

        $result = $this->model->withLuckyBoxFlag($user->id)
            ->select(['id', 'uid', 'room_name', 'room_cover', 'room_intro', 'room_status', 'room_pass', 'room_admin', 'room_visitor', 'room_black', 'room_speak', 'room_sound', 'microphone', 'free_mic', 'max_admin', 'is_recommended', 'is_popular', 'is_live', 'hot', 'pin', 'top_room', 'hour_hot', 'type', 'mode', 'created_at'])
            ->with([
            'backgroundImage:request_background_images.id,owner_room_id,img',
            'lastPk:id,room_id',
            'background:id',
            'roomVisitorUsers' => fn($q) => $q->limit(5),
            'myClass',
            'roomCategory:id,type',
            'myType',
            'roomVisitors.user.packs',
            'owner.enabledMedals',
            'owner.country',
            'owner.eligiblePacks.ware',
            'owner.profile',
            'owner.medals.achievementLevel.achievement',
            'boxUse',
        ])
        ->withCount('roomVisitors')
        ->whereHas('owner')
        ->whereNotIn('uid', $blockedUserIds)
//        ->whereDoesntHave('owner.packs', function ($q) {
//            $q->where('type', 16)
//                ->where('is_used', 1)
//                ->where(function ($q) {
//                    $q->where('expire', 0)
//                        ->orWhere('expire', '>=', now()->timestamp);
//                });
//        })
        ->where('room_status', 1);

        $result->orderByDesc('pin');

        if ($topRooms) {
            $result->orderByRaw('is_top = 1 DESC');
        }else {
            $result->where(function ($query) {
                $query->whereHas('roomVisitors')->orWhere('pin', 1);
            });
        }
        $result->orderByDesc('room_visitors_count');

        $result->orderByDesc('hour_hot');

        if (!is_null($req->country_id)) {
            $result->whereHas('owner', function ($q) use ($req) {
                $q->where('country_id', $req->country_id);
            });
        }

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
                $result->orderByDesc('top_room');
                break;

            case 'last_create':
                $result->whereDate('created_at', '>=', Carbon::now()->subDays(3))
                    ->orderByDesc('id');
                break;

            case 'pk':
                $result->has('lastPk');
                break;

            case 'party':
                $result->whereHas('roomCategory', function ($query) {
                    $query->where('type', 'party');
                });
                break;

            case 'recently':
            case 'festival':
                $result->orderByDesc('top_room')
                    ->orderByDesc('session');
                break;

            case 'interested':
                $roomTypes = EnteredRoom::query()
                    ->where('uid', $user->id)
                    ->where('entered_at', '>=', Carbon::now()->subDay())
                    ->with('room')
                    ->get()
                    ->pluck('room.room_type')
                    ->unique();

                $result->whereIn("room_type", $roomTypes)
                    ->orderByDesc('top_room')
                    ->orderByDesc('session');
                break;

            case 'following':
                $result->whereIn('uid', $user->followeds_ids())
                    ->orderByDesc('top_room')
                    ->orderByDesc('session');
                break;

            case 'friends':
                $result->whereIn('uid', $user->friends_ids())
                    ->orderByDesc('top_room')
                    ->orderByDesc('session');
                break;

            case 'nearby':
                $userLat = $user->lat;
                $userLong = $user->long;

                $result->selectRaw(
                    'rooms.*,
                    ( 6371 * acos( cos( radians(?) ) * cos( radians( owner.lat ) ) * cos( radians( owner.long ) - radians(?) ) + sin( radians(?) ) * sin( radians( owner.lat ) ) ) ) AS distance',
                    [$userLat, $userLong, $userLat]
                )
                ->join('users as owner', 'rooms.uid', '=', 'owner.id')
                ->orderBy('distance');
                break;
        }

        if (count($ids) > 0) {
            $result = $result->whereIn('uid', $ids);
        }

        return $result->when($roomType != 'live', function ($q) use ($roomType) {
            $q->where('type', $roomType);
        })->when($roomType == 'live', function ($q) use ($roomType) {
            $q->whereIn('type', ['single_live', 'multi_live']);
            })->aginate(10);
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
