<?php

namespace App\Services;

use App\Helpers\Common;
use App\Models\AppFeature;
use App\Repositories\RankingRepository;
use App\Tik\Repositories\GiftLogRepository;
use App\Http\Resources\Api\V1\MangerTypeResource;
use Modules\Achievement\Http\Services\UserAchievementService;
use Modules\Achievement\Transformers\UserAchievementLevelsResource;
use App\Models\User;

class RankingService
{
    protected $rankingRepo;

    public function __construct(
        RankingRepository $rankingRepo,
        private readonly GiftLogRepository $GiftLogRepository,
        public UserAchievementService $achievementService
    ) {
        $this->rankingRepo = $rankingRepo;
    }


    public function getRoomRanking($roomOwnerId, $type, $limit, $userId)
    {
        $data = $this->GiftLogRepository->getRoomRankingData($roomOwnerId, $type, $limit);
        $position = 0;
        $currentUserRank = 0;

        foreach ($data as $index => $item) {
            $user = $item->sender;

            if (!$user) {
                $this->setDefaultUserData($item);
                continue;
            }

            $position++;
            $this->populateUserData($item, $user);

            if ($user->id == $userId) {
                $currentUserRank = $position;
            }

            unset($item->sender); // Remove unnecessary loaded relation
        }

        unset($item);

        return $data->toArray();
    }

    private function setDefaultUserData(&$item)
    {
        $item->user_id  = 0;
        $item->name     = '';
        $item->avatar   = '';
        $item->frame    = '';
        $item->frame_id = 0;
        $item->type_user = 0;
        $item->manger_type = null;
        $item->vip = null;
        $item->has_color_name = null;
        $item->data_achivement = null;
    }

    private function populateUserData(&$item, $user)
    {
        $item->user_id  = $user->id;
        $item->name     = $user->name;
        $item->avatar   = optional($user->profile)->avatar ?? '';
        $item->frame    = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true);
        $item->frame_id = $user->dress_1 ?? 0;
        $item->type_user = intval($user->type_user ?? 0);
        $item->manger_type = $user->mangerType ? new MangerTypeResource($user->mangerType) : null;
        $item->vip = Common::ovip_center($user);
        $item->has_color_name = Common::hasInPack($user->id, 18, true);
        $item->data_achivement = UserAchievementLevelsResource::collection(
            $this->achievementService->getUserAchievement($user)
        );
    }
    public function getRanking($class, $type, $user, $limit, $room_uid, $sent_to_owner)
    {
        if ($class == 4) {
            $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
            $this->transformData($data, $class, 'user_id', 'user');
            return $this->prepareResponse($data, $user, $type, 'user_id', $user->id, $class, $limit);
        }

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogs($class, $rel, $type, $limit, $keywords);
        $this->transformData($data, $class, $keywords, $rel);

        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit);
    }

    protected function transformData(&$data, $class, $key, $relation)
    {
        $data = $data->reject(function ($q) {
            return $q->exp == 0;
        });

        $data = $data->values()->map(function ($item, $key) use ($data) {
            if ($key === 0) {
                $item->exp_diff = 0;
            } else {
                $item->exp_diff = $data[$key - 1]->exp - $item->exp  + 1;
            }
            return $item;
        });


        $data = $data->map(function ($v) use ($key, $class, $relation) {

            $user = $v->$relation;

            if ($user == null) {
                return null;
            }

            $v->user_id = $user->id;
            $value = $v->exp;
            $v->exp = numToString(ceil($v->exp));
            $v->exp_int = ceil($value);

            $value2 = $v->exp_diff;
            $v->remaining = numToString(ceil($v->exp_diff));
            $v->remaining_int = ceil($value2);

            $v->name = $class == 3 ? (@$user->ownerRoom?->room_name ?? '') : $user->name;
            $v->avatar = $class == 3 ? (@$user->ownerRoom?->room_cover ?? '') : $user->profile->avatar;
            $v->frame = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true) ?: Common::getUserDress($user->id, $user->dress_1, 4, 'img1', true);
            $v->frame_id = $user->dress_1;
            $v->type_user =  intval(@$user->type_user) ?: 0;
            $v->manger_type =  !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);

            $v->vip_level = @$user->UserVip->level ?? 0;
            $v->sender_level = @$user->total_sender_level;
            $v->reciver_level = @$user->total_received_level;

            $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
            $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);

            $v->vip_level_img = @$user->UserVip?->OVip?->img ?? 0;
            $v->sender_level_img = @$total_received_level_img->img;
            $v->reciver_level_img = @$total_sender_level_img->img;

            $v->country = @$user->country;
            unset($v->$relation);
            return $v;
        })->reject(function ($v) {
            return $v == null;
        });
    }



    protected function prepareResponse($data, $user, $type, $key, $userId, $class, $limit, $userExp = null)
    {
        $kong['user_id']    = 0;
        $kong['uuid']       = '';
        $kong['exp']        = '0';
        $kong['exp_int']        = 0;
        $kong['remaining']        = '0';
        $kong['remaining_int']        = 0;
        $kong['name']       = '';
        $kong['avatar']     = '';
        $kong['frame']      = '';
        $kong['frame_id']   = 0;
        $kong['sender_img'] = '';
        $kong['reseverimg'] = '';
        $kong['vip_level'] = '';
        $kong['sender_level'] = 0;
        $kong['reciver_level'] = 0;

        $kong['vip_level_img'] = '';
        $kong['sender_level_img'] = '';
        $kong['reciver_level_img'] = '';

        $kong['type_user'] = 0;
        $kong['manger_type'] = null;

        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        //        if ($limit == 3) return $data;


        $user->sort = $this->getUserSortValue($data, $userId);
        $user->user_id = $user->id;

        $arr['user'] = $user->only('user_id', 'uuid', 'exp', 'name', 'avatar', 'frame', 'frame_id', 'manger_type_id');

        $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
        $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
        $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);
        $vip_level  = Common::ovip_center_rank($arr['user']['user_id']);
        $vip_level_img  = Common::ovip_center_rank_img($arr['user']['user_id']);
        // $levels =Common::getSenderAndReceiverLevels($user->id);
        if (gettype($vip_level) != 'integer') {
            $vip_level = 0;
        }
        $arr['user']['exp'] = $userExp->exp ?? '0';
        $arr['user']['sender_img'] = $sender_img;
        $arr['user']['vip_level']  = $vip_level == 0 ? '' : $vip_level;
        $arr['user']['sender_level']  = $user->total_sender_level;
        $arr['user']['reciver_level']  = $user->total_received_level;
        $arr['user']['vip_level_img']  = $vip_level_img->img ?? '';
        $arr['user']['sender_level_img']  = $total_sender_level_img->img ?? '';
        $arr['user']['reciver_level_img']  = $total_received_level_img->img ?? '';
        $arr['user']['type_user'] =  intval(@$user->type_user) ?: 0;
        $arr['user']['country'] =  @$user->country;
        $arr['user']['manger_type'] = !$user->mangerType ? null : new MangerTypeResource(@$user->mangerType);


        $toArray = $data->toArray();
        $countData = count($data);
        $arr['top'] = $countData < 4 ? $data : array_slice($toArray, 0, 3);
        $arr['other'] = $countData < 4 ? [] : array_slice($toArray, 3);
        return $arr;
    }

    protected function getClassKeywordsAndRelation($class)
    {
        if ($class == 1) {
            return ['receiver_id', 'receiver'];
        } elseif ($class == 2) {
            return ['sender_id', 'sender'];
        } elseif ($class == 3) {
            return ['roomowner_id', 'roomOwner'];
        } else {
            return ['sender_id', 'sender'];
        }
    }

    private function getUserSortValue($data, $user_id)
    {
        $sort = 0;
        foreach ($data as $i => $v) {
            if (isset($v->receiver_id) && $v->receiver_id == $user_id) {
                $sort = $i + 1;
                break;
            }
        }
        return $sort ? (string) $sort : '99+';
    }

    public function topUser()
    {
        $giftLogs = $this->GiftLogRepository->topUser('sender', 'sender_id');
        $giftLogsReceiver = $this->GiftLogRepository->topUser('receiver', 'receiver_id');
        $giftLogsRooms = $this->GiftLogRepository->topUser('roomOwner', 'roomowner_id');
        $img      = [];
        foreach ($giftLogs as $giftLog) {
            $img[] = $giftLog->sender->profile->avatar ?? '';
        }

        $receiverImage = [];
        foreach ($giftLogsReceiver as $giftLog) {
            $receiverImage[] = $giftLog->receiver->profile->avatar ?? '';
        }

        $roomImage = [];
        foreach ($giftLogsRooms as $giftLogsRoom) {
            $roomImage[] = $giftLogsRoom->roomOwner->ownerRoom->room_cover ?? '';
        }
        return Common::apiResponse(1, '', ['sender' => $img, 'receiver' => $receiverImage, 'room' => $roomImage]);
    }

    public function topUser2()
    {
        $giftLogs = $this->GiftLogRepository->topUser('sender.profile', 'sender_id');
        $giftLogsReceiver = $this->GiftLogRepository->topUser('receiver.profile', 'receiver_id');
        $giftLogsRooms = $this->GiftLogRepository->topUser('roomOwner', 'roomowner_id');
        $img      = [];
        foreach ($giftLogs as $giftLog) {
            $img[] = $giftLog->sender->profile->avatar ?? '';
        }

        $receiverImage = [];
        foreach ($giftLogsReceiver as $giftLog) {
            $receiverImage[] = $giftLog->receiver->profile->avatar ?? '';
        }

        $roomImage = [];
        foreach ($giftLogsRooms as $giftLogsRoom) {
            $roomImage[] = $giftLogsRoom->roomOwner->ownerRoom->room_cover ?? '';
        }
        return Common::apiResponse(1, '', ['sender' => $img, 'receiver' => $receiverImage, 'room' => $roomImage]);
    }
    public function getRankingOneRoom($class, $type, $user, $limit, $room_id, $sent_to_owner)
    {

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords);
        $userExp = $this->rankingRepo->getGiftLogsUserForRoomOwnerId($class, $rel, $type, $user->id, $room_id, $keywords);
        $this->transformData($data, $class, $keywords, $rel);
        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit, $userExp);
    }


    public function getRankingOneRoom2($class, $type, $user, $limit, $room_id, $sent_to_owner)
    {

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords);
        $userExp = $this->rankingRepo->getGiftLogsUserForRoomOwnerId($class, $rel, $type, $user->id, $room_id, $keywords);
        $this->transformData2($data, $class, $keywords, $rel);
        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit, $userExp);
    }

    protected function transformData2(&$data, $class, $key, $relation)
    {
        $data = $this->removeZeroExp($data);
        $data = $this->calculateExpDifference($data);
        $data = $this->mapUserData($data, $class, $relation);

        // Re-index values after mapping and rejecting nulls
        $data = $data->values();
    }


    private function removeZeroExp($data)
    {
        return $data->reject(fn($item) => $item->exp == 0);
    }

    private function calculateExpDifference($data)
    {
        return $data->values()->map(function ($item, $index) use ($data) {
            $item->exp_diff = $index === 0 ? 0 : $data[$index - 1]->exp - $item->exp + 1;
            return $item;
        });
    }

    private function mapUserData($data, $class, $relation)
    {
        return $data->map(function ($item) use ($class, $relation) {
            $user = $item->$relation;

            if (!$user) {
                return null; // Skip if user data is missing
            }

            $this->populateUser2Data($item, $user, $class);

            // Safely remove the relation property
            if (property_exists($item, $relation)) {
                unset($item->$relation);
            }
            return $item;
        })->reject(fn($item) => is_null($item));
    }

    private function populateUser2Data(&$item, $user, $class)
    {
        $item->user_id = $user->id;

        // EXP transformations
        $item->exp_int = ceil($item->exp);
        $item->exp = numToString($item->exp_int);

        // Remaining EXP
        $item->remaining_int = ceil($item->exp_diff);
        $item->remaining = numToString($item->remaining_int);

        // Name and Avatar handling
        $item->name = $class == 3 ? optional($user->ownerRoom)->room_name ?? '' : $user->name;
        $item->avatar = $class == 3 ? optional($user->ownerRoom)->room_cover ?? '' : optional($user->profile)->avatar;

        // Frame and User Dress
        $item->frame = Common::getUserDress($user->id, $user->dress_1, 4, 'img2', true)
            ?: Common::getUserDress($user->id, $user->dress_1, 4, 'img1', true);
        $item->frame_id = $user->dress_1;

        // Additional User Info
        $item->type_user = intval(optional($user)->type_user) ?: 0;
        $item->manger_type = $user->mangerType ? new MangerTypeResource($user->mangerType) : null;

        $item->vip_level = optional($user->UserVip)->level ?? 0;
        $item->sender_level = $user->total_sender_level ?? 0;
        $item->reciver_level = $user->total_received_level ?? 0;
        $item->country = $user->country ?? null;
    }

    public function getRanking2($class, $type, $user, $limit, $room_uid, $sent_to_owner)
    {
        if ($class == 4) {
            $data = $this->rankingRepo->getUserLuckyGifts($type, $limit);
            $this->transformData2($data, $class, 'user_id', 'user');
            return $this->prepareResponse($data, $user, $type, 'user_id', $user->id, $class, $limit);
        }

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogs($class, $rel, $type, $limit, $keywords);
        $this->transformData2($data, $class, $keywords, $rel);

        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit);
    }
}
