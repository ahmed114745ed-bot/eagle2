<?php

namespace Utd\Room\Repositories;

use Carbon\Carbon;
use App\Models\Pack;
use App\Models\User;
use Utd\Room\Entities\Room;
use Utd\Room\Entities\EnteredRoom;
use Utd\Room\Entities\RoomPrivateMessages;
use App\Http\Resources\Api\V1\NowRoomResource;
use App\Http\Resources\Api\V1\RoomResource;
use App\Contracts\RoomRepositoryContract;
use App\Helpers\CacheHelper;

/**
 * @property Room $model
 */
class RoomRepository extends AbstractRepository implements RoomRepositoryContract, RoomRepoInterface
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

    public function createPrivetMessage($fromUserId, $toUserId, $message, $price)
    {
        return RoomPrivateMessages::query()->create([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'message' => $message,
            'price' => $price
        ]);
    }

    public function all($req, $ids = [])
    {
        $roomType = $req->room_type ?? 'audio';
        $user = $req?->user();
        $topRooms = (settings()->get('make_rooms_top') == 1) ?? false;

        $blockedUserIds = $this->getBlockedUserIds();

        $result = $this->model->withLuckyBoxFlag($user->id)
            ->select(['id', 'uid', 'room_name', 'room_background', 'room_cover', 'room_intro', 'room_status', 'room_pass', 'room_admin', 'room_visitor', 'room_black', 'room_speak', 'room_sound', 'microphone', 'free_mic', 'max_admin', 'is_recommended', 'is_popular', 'is_live', 'hot', 'pin', 'top_room', 'hour_hot', 'type', 'mode', 'created_at'])
            ->with([
                'backgroundImage:request_background_images.id,owner_room_id,img',
                'defaultBackground:id,img',
                'lastPk:id,room_id',
                'background:id,img',
                'roomVisitorUsers' => fn($q) => $q->with('profile')->limit(5),
                'myClass',
                'roomCategory:id,type',
                'myType',
                'roomVisitors',
                'boxUse',
                'owner.agency.owner',
                'owner' => [
                    'enabledMedals',
                    'agency',
                    'country',
                    'color_image',
                    'specialId.ware',
                    'eligiblePacks.ware',
                    'profile',
                    'medals.achievementLevel.achievement'
                ],
            ])
            ->withCount('roomVisitors')
            ->whereHas('owner')
            ->whereNotIn('uid', $blockedUserIds)
            ->where('room_status', 1);

        $result->orderByDesc('pin');

        if ($topRooms && $roomType != 'live') {
            $result->orderByRaw('is_top = 1 DESC');
        } else {
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
        })->paginate(10);
    }

    public function mine($req, $id)
    {
        $user = User::find($id);
        $query = $this->baseRoomQueryMyMine($user);

        $audio = (clone $query)->where('type', 'audio')->first();
        $live = (clone $query)->where('type', 'live')->first();

        return [
            'audio' => $audio
                ? new RoomResource($audio)
                : (object)[],

            'live' => $live
                ? new RoomResource($live)
                : (object)[],
        ];
    }

    public function getUserRooms($req, $id)
    {
        $user = User::find($id);
        $query = $this->baseRoomQueryMine($user);

        $audio = (clone $query)->where('type', 'audio')->first();
        $live = (clone $query)->where('type', 'live')->where('is_live', true)->first();
        $nowRooms = $this->getNowRooms($user);

        return [
            'audio' => $audio
                ? new RoomResource($audio)
                : (object)[],

            'live' => $live
                ? new RoomResource($live)
                : (object)[],
            'now_room' => $nowRooms
                ? $nowRooms
                : (object)[],
        ];
    }

    private function getNowRooms($user)
    {
        if (!$user->now_room_uid) return (object)[];

        $nowRoomOwner = $user->nowRoomOwner;

        if (!$nowRoomOwner) return (object)[];

        if ($nowRoomOwner->getPackWithTypeV3(16)) return (object)[];

        $resource = (new NowRoomResource($this))->toArray(request());

        return empty($resource) ? (object)[] : $resource;
    }

    private function baseRoomQuery($user, $blockedUserIds)
    {
        return $this->model
            ->where('type', 'live')
            ->where('is_live', true)
            ->select([
                'id',
                'uid',
                'room_name',
                'room_cover',
                'room_intro',
                'room_status',
                'room_pass',
                'room_admin',
                'room_visitor',
                'room_black',
                'room_speak',
                'room_sound',
                'microphone',
                'free_mic',
                'max_admin',
                'is_recommended',
                'is_popular',
                'is_live',
                'hot',
                'pin',
                'top_room',
                'hour_hot',
                'type',
                'mode',
                'created_at'
            ])
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
            ->whereHas('roomVisitors', function ($query) {
                $query->whereColumn('user_id', 'rooms.uid');
            })
            ->where('room_status', 1)
            ->orderByDesc('pin')
            ->orderByDesc('room_visitors_count')
            ->orderByDesc('hour_hot');
    }

    private function baseRoomQueryMyMine($user)
    {
        return $this->model
            ->where('uid', $user->id)
            ->select([
                'id',
                'uid',
                'room_name',
                'room_cover',
                'room_intro',
                'room_status',
                'room_pass',
                'room_admin',
                'room_visitor',
                'room_black',
                'room_speak',
                'room_sound',
                'microphone',
                'free_mic',
                'max_admin',
                'is_recommended',
                'is_popular',
                'is_live',
                'hot',
                'pin',
                'top_room',
                'hour_hot',
                'room_background',
                'type',
                'mode',
                'created_at'
            ])
            ->with([
                'backgroundImage:request_background_images.id,owner_room_id,img',
                'lastPk:id,room_id',
                'background:id,img',
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
                'taskStream',
                'taskStreamRoom'
            ])
            ->withCount('roomVisitors')
            ->orderByDesc('pin')
            ->orderByDesc('room_visitors_count')
            ->orderByDesc('hour_hot');
    }

    private function baseRoomQueryMine($user)
    {
        $authUserId = auth()->id();

        return $this->model
            ->where('uid', $user->id)
            ->select([
                'id',
                'uid',
                'room_name',
                'room_cover',
                'room_intro',
                'room_status',
                'room_pass',
                'room_admin',
                'room_visitor',
                'room_black',
                'room_speak',
                'room_sound',
                'microphone',
                'free_mic',
                'max_admin',
                'is_recommended',
                'is_popular',
                'is_live',
                'hot',
                'pin',
                'top_room',
                'hour_hot',
                'room_background',
                'type',
                'mode',
                'created_at'
            ])
            ->with([
                'backgroundImage:request_background_images.id,owner_room_id,img',
                'lastPk:id,room_id',
                'background:id,img',
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
                'owner.chatRoomsAsUser' => function ($q) use ($authUserId) {
                    $q->where('user_id2', $authUserId)
                        ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
                            $query->where('user_id', '<>', $authUserId)
                                ->where('status', '<>', 'seen');
                        }]);
                },
                'owner.chatRoomsAsUser2' => function ($q) use ($authUserId) {
                    $q->where('user_id', $authUserId)
                        ->withCount(['messages as unread_messages_count' => function ($query) use ($authUserId) {
                            $query->where('user_id', '<>', $authUserId)
                                ->where('status', '<>', 'seen');
                        }]);
                },
            ])
            ->withCount('roomVisitors')
            ->where('room_status', 1)
            ->orderByDesc('pin')
            ->orderByDesc('room_visitors_count')
            ->orderByDesc('hour_hot');
    }

    private function applyTopRoomsOrder($query, $topRooms)
    {
        if ($topRooms) {
            $query->orderByRaw('is_top = 1 DESC');
        } else {
            $query->where(function ($q) {
                $q->whereHas('roomVisitors')->orWhere('pin', 1);
            });
        }
    }

    private function applyCountryFilter($query, $countryId)
    {
        $query->whereHas('owner', fn($q) => $q->where('country_id', $countryId));
    }

    public function liveRooms($req, $ids = [])
    {
        $user = $req?->user();
        $topRooms = false;

        $blockedUserIds = $this->getBlockedUserIds();

        $query = $this->baseRoomQuery($user, $blockedUserIds);

        $this->applyTopRoomsOrder($query, $topRooms);

        if (!is_null($req->country_id)) {
            $this->applyCountryFilter($query, $req->country_id);
        }

        if (!empty($ids)) {
            $query->whereIn('uid', $ids);
        }
        return RoomResource::collection(
            $query->where('type', 'live')->where('is_afk', 1)->paginate()
        );
    }

    // ========================================
    // Methods from RoomRepoInterface
    // ========================================

    /**
     * Find room by owner user ID
     */
    public function find($id)
    {
        return $this->model->where('uid', $id)->first();
    }

    /**
     * Find room by owner ID and type
     */
    public function findByType($id, $type)
    {
        return $this->model->where('uid', $id)->where('type', $type)->first();
    }

    /**
     * Save model and clear cache
     */
    public function save($model)
    {
        CacheHelper::forget('rooms');
        $model->save();
        return $model;
    }

    /**
     * Delete room by ID
     */
    public function delete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    /**
     * Get all opening rooms
     */
    public function getAllOpening()
    {
        return $this->model
            ->orderBy('top_room', 'DESC')
            ->where('room_status', 1)
            ->get();
    }

    /**
     * Get all opening room IDs
     */
    public function getAllOpeningIds()
    {
        return $this->model
            ->where('room_status', 1)
            ->pluck('id')
            ->toArray();
    }

    // ========================================
    // DB Query Helper Methods (Issue #10)
    // ========================================

    /**
     * Get single column value by uid
     */
    public function getValueByUid($uid, $column)
    {
        return \DB::table('rooms')->where('uid', $uid)->value($column);
    }

    /**
     * Get room data with specific columns by uid
     */
    public function getColumnsByUid($uid, $columns)
    {
        return \DB::table('rooms')->where('uid', $uid)->select($columns)->first();
    }

    /**
     * Update room by uid
     */
    public function updateByUid($uid, $data)
    {
        return \DB::table('rooms')->where('uid', $uid)->update($data);
    }

    /**
     * Get microphone status data
     */
    public function getMicrophoneStatusByUid($uid)
    {
        return (array)\DB::table('rooms')
            ->selectRaw("uid,microphone,is_prohibit_sound,room_sound,play_num")
            ->where('uid', $uid)
            ->first();
    }

    /**
     * Get room mic info
     */
    public function getMicInfoByUid($uid)
    {
        return (array)\DB::table('rooms')
            ->where(['uid' => $uid])
            ->selectRaw('id,room_visitor,room_admin,microphone,free_mic,mode')
            ->first();
    }

    /**
     * Get room user info (admin, speak, judge, sound)
     */
    public function getRoomUserInfoByUid($uid)
    {
        return \DB::table('rooms')->where('uid', $uid)->select([
            'room_admin',
            'room_speak',
            'room_judge',
            'room_sound'
        ])->get()->toArray();
    }

    /**
     * Get room black and id by uid
     */
    public function getRoomBlackByUid($uid)
    {
        return \DB::table('rooms')->where('uid', $uid)->first();
    }
}
