<?php

namespace App\Tik\Services;


use Exception;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Helpers\Common;
use App\Tik\Repositories\OvipRepository;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\WareRepository;
use App\Tik\Repositories\ImageRepository;
use App\Tik\Repositories\TicketRepository;
use App\Tik\Repositories\GiftLogRepository;
use App\Tik\Repositories\UserVipRepository;
use App\Tik\Repositories\LiveTimeRepository;


class HomeService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly LiveTimeRepository $liveTimeRepository,
        private readonly GiftLogRepository $giftLogRepository,
        private readonly ImageRepository $imageRepository,
        private readonly OvipRepository $ovipRepository,
        private readonly WareRepository $wareRepository,
        private readonly RoomRepository $roomRepository,
        private readonly PackRepository $packRepository,
        private readonly UserVipRepository $userVipRepository,
        private readonly TicketRepository $ticketRepository,
    ) {
    }

    public function totalHours($request, $userId)
    {
        $user  = $this->userRepository->findById($userId);
        $today = false;

        if ($request->time == 'today') {

            $userHours = $this->liveTimeRepository->totalHoursUser($userId);
            $totalTime = Common::totalTime($userHours);


            $days = $user->today_days;
            if ($days >= 1) {
                $today = true;
            }
            $diamonds = $this->giftLogRepository->getSumOfReceiverObtain($userId);
            $type = 0;
        } elseif ($request->time == 'month') {
            $userHours = $this->liveTimeRepository->totalHoursByMonth($userId);
            $totalTime = Common::totalTime($userHours);

            $days = $user->monthly_days + $user->today_days;
            $diamonds =  $user->user_diamond; // userDiamond
            $type = 1;
        } else {
            $userHours = $this->liveTimeRepository->totalHours($userId);
            $totalTime = Common::totalTime($userHours);
            $days = $user->total_days + $user->today_days;
            $diamonds = $user->total_diamond_received;
            $type = 3;
        }

        return [$user, $diamonds, $days, $type, $totalTime, $today];
    }

    public function imageIndex()
    {
        $pk_images = $this->imageRepository->getImage();
        $vip_images = $this->ovipRepository->getOvip();
        foreach ($vip_images as $k => &$image) {
            $image->frame = $this->wareRepository->getWithType(4, $image->level);
            $image->intro = $this->wareRepository->getWithType(6, $image->level);
        }
        return [$pk_images, $vip_images];
    }

    public function wapel($userId, $ownerId)
    {
        $room = $this->roomRepository->findRoomUser($ownerId);
        if (!$room) throw new Exception('room not found');
        $vip = $this->userVipRepository->findByUserId($userId);

        if (!$vip) throw new Exception('not found');
        $level =  $vip->OVip->level;
        $wapel = $this->packRepository->findByUserId($userId, 12);

        if ($wapel) {
            $expire = $wapel->expire;

            $wapel->use_num -= 1;
            $wapel->save();
            if ($wapel->use_num < 1) {
                $wapel->delete();
            }
            $ware = $this->wareRepository->findById($wapel->target_id);
        }
        return [$level, $expire, $ware, $room->id,$wapel];
    }

    public function openTicket($request)
    {
        $data = [
            'user_id' => $request->user_id,
            'contact_num' => $request->contact,
            'problem' => $request->txt,
            'description' => $request->description,
            'status' => 1
        ];
        $tkt = $this->ticketRepository->create($data);
        if ($request->hasFile('img')) {
            $img = $request->file('img');
            $path = Common::upload('ticket', $img);
            $tkt->img = $path;
            $tkt->save();
        }

        return  $tkt;
    }

    public function changePackMode($type, $privilegeArr, User $user, $isAvailable)
    {
        if (key_exists($type, $privilegeArr)) {
            $privilegeId = $privilegeArr[$type];

            if ($isAvailable && !Ware::query()->where('type', $privilegeId)->exists()) {
                return Common::apiResponse(0, 'not found', null, 404);
            } else if (!Pack::query()->where('user_id', $user->id)->where('type', $privilegeId)->where('is_used', !$isAvailable)->exists()) {

                return Common::apiResponse(0, 'not allowed', null, 403);
            }
            $pack = Pack::query()->where('user_id', $user->id)->where('type', $privilegeId)->first();
            dd( $pack);
            Pack::query()->where('user_id', $user->id)->where('type', $privilegeId)->update(['is_used' => $isAvailable]);

            switch ($type) {
                case 'country':
                    /*if ($isAvailable) {
                        $user->country_id = null;
                        $user->save();
                    }*/
                    break;
                case 'room':
                    Room::query()->where('uid', $user->id)->update(['room_status' => $isAvailable ? 2 : 1]);
                    break;
            }
        }
        return true;
    }
}
