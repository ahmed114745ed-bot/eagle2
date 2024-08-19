<?php

namespace App\Services;


use App\Helpers\Common;
use App\Tik\Repositories\VipRepository;
use App\Tik\Repositories\OvipRepository;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\WareRepository;
use App\Tik\Repositories\UserVipRepository;
use App\Tik\Repositories\VipPrivilegeRepository;



class VipService
{
    public function __construct(
        private readonly VipRepository $vipRepository,
        private readonly OvipRepository $ovipRepository,
        private readonly VipPrivilegeRepository $vipPrivilegeRepository,
        private readonly UserRepository $userRepository,
        private readonly UserVipRepository $userVipRepository,
        private readonly PackRepository $packRepository,
        private readonly WareRepository $wareRepository,

    ) {}

    public function vipIndex($type)
    {
        return $this->vipRepository->getByType($type);
    }

    public function vipList()
    {
        $vipPrivileges = $this->vipPrivilegeRepository->all();
        $oVips = $this->ovipRepository->getBySortLevel();


        $wares = $this->wareRepository->getOVip($oVips->pluck('level'), $vipPrivileges->pluck('type'));
        return $oVips->each->setRelation('wares', $wares);
    }

    public function buyVip($request)
    {
        $vip = $this->ovipRepository->findById($request->vip_id);
        if (!$vip) throw new \Exception('not found');
        $qty = $request->qty ?: 1;
        $total = $vip->price * $qty;
        $expire = $vip->expire;
        if ($expire == 0) {
            $ex = 0;
        } else {
            $ex = now()->addDays($expire * $qty)->timestamp;
        }
        if ($request->type == 1) {
            $type = 1;
            if (!$request->to_user) throw new \Exception('missing param');
            $userUuId = $request->to_user;
            $user = $this->userRepository->searchUser($userUuId);
            if (!$user) throw new \Exception('not found');
            if ($user->phone == null || $user->phone == '') throw new \Exception(__('api.phone'));
            $user_id = $user->id;
            $sender = $request->user();
            $sender_id = $sender->id;
            $from = $sender;
        } else {
            $type = 0;
            $user = $request->user();
            $user_id = $user->id;
            $sender_id = 0;
            if ($user->di < $total) throw new \Exception('balance low');
            $from = $user;
        }
        $this->userRepository->decrementUserCoins($from, $total);
        $this->packRepository->deleteExpirePack();


        $data = [
            'type' => $type,
            'sender_id' => $sender_id,
            'user_id' => $user_id,
            'vip_id' => $vip->id,
            'level' => $vip->level,
            'expire' => $ex,
            'qty' => $qty,
            'price' => $vip->price,
            'total' => $total,
            'is_used' => 0
        ];
        $this->userVipRepository->create($data);
        $countWares = $this->wareRepository->countWareByLevel($vip->level);
        return [$user, $countWares, $request->user(), $vip->exp];
    }

    public function userVip($request)
    {
        $user_vip = $this->userVipRepository->findByIdWithOVip($request->vip_id);

        if (!$user_vip)  throw new \Exception(__("api_responses.vip_not_found"));

        $user = $request->user();

        $isUsed = (bool)$request->type;
        if ($isUsed) $this->userVipRepository->updateIsUsedForUser($user->id);

        // update is used
        $this->userVipRepository->updateIsUsedWithNum($user_vip, $isUsed);

        $vip = $user_vip->OVip;
        if ($user_vip->num_used <= 1) {
            // add vip data to user
            Common::handelVip($vip, $user);
        }
        return  $data['target_id'] = $user_vip->id;
    }

    public function sendVip($request)
    {
        $from = $request->user();
        $user_vip = $this->userVipRepository->findById($request->vip_id);
        if (!$user_vip || $user_vip->user_id != $from->id)  throw new \Exception(__("api_responses.vip_not_found"));

        if ($user_vip->is_used == 1  || $user_vip->num_used >= 1) throw new \Exception('ال vip مستخدم من قبل لا يمكن اهدائه');

        $data = [
            'sender_id' => $from->id,
            'user_id' => $request->user_id,
        ];
        $this->userVipRepository->update($data, $user_vip->id);
        return $user_vip;
    }
}