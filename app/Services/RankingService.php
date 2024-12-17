<?php

namespace App\Services;

use App\Helpers\Common;
use App\Models\AppFeature;
use App\Repositories\RankingRepository;
use App\Tik\Repositories\GiftLogRepository;
use App\Http\Resources\Api\V1\MangerTypeResource;
use App\Models\User;

class RankingService
{
    protected $rankingRepo;

    public function __construct(
        RankingRepository $rankingRepo,
        private readonly GiftLogRepository $GiftLogRepository
    ) {
        $this->rankingRepo = $rankingRepo;
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
        $kong['vip_level'] = 0;
        $kong['sender_level'] = 0;
        $kong['reciver_level'] = 0;
        $kong['type_user'] = 0;
        $kong['manger_type'] = null;

        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        //        if ($limit == 3) return $data;


        // $user->sort = $this->getUserSortValue($data, $userId);
        // $user->user_id = $user->id;

        $arr['user'] = $user->only('user_id', 'uuid', 'exp', 'name', 'avatar', 'frame', 'frame_id', 'manger_type_id');

        $sender_img = @$user->getImageReceiverOrSender('sender_id', 2)?->img ?? '';
        $total_received_level_img = Common::getImageTotalReceiverOrSender($user->total_received_level);
        $total_sender_level_img = Common::getImageTotalReceiverOrSender($user->total_sender_level);
        $vip_level  = Common::ovip_center_rank($arr['user']['user_id']);
        $vip_level_img  = Common::ovip_center_rank_img($arr['user']['user_id']);
        // $levels =Common::getSenderAndReceiverLevels($user->id);
        // if (gettype($vip_level) != 'integer') {
        //     $vip_level = 0;
        // }
        $arr['user']['exp'] = $userExp->exp ?? '0';
        $arr['user']['sender_img'] = $sender_img;
        $arr['user']['vip_level']  = $vip_level;
        $arr['user']['sender_level']  = $user->total_sender_level;
        $arr['user']['reciver_level']  = $user->total_received_level;
        $arr['user']['vip_level_img']  = $vip_level_img->img;
        $arr['user']['sender_level_img']  = $total_sender_level_img->img;
        $arr['user']['reciver_level_img']  = $total_received_level_img->img;
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
    public function getRankingOneRoom($class, $type, $user, $limit, $room_id, $sent_to_owner)
    {

        [$keywords, $rel] = $this->getClassKeywordsAndRelation($class);

        $data = $this->rankingRepo->getGiftLogsForRoomOwnerId($class, $rel, $type, $limit, $room_id, $keywords);
        $userExp = $this->rankingRepo->getGiftLogsUserForRoomOwnerId($class, $rel, $type, $user->id, $room_id, $keywords);
        $this->transformData($data, $class, $keywords, $rel);
        return $this->prepareResponse($data, $user, $type, $keywords, $user->id, $class, $limit, $userExp);
    }


    
}
