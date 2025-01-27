<?php

namespace App\Services;


use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use App\Tik\Repositories\VipRepository;
use App\Tik\Repositories\OvipRepository;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\WareRepository;
use App\Tik\Repositories\UserVipRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Tik\Repositories\VipPrivilegeRepository;
use Illuminate\Support\Facades\Log;

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
        $oVips = $oVips->map(function ($oVip) use ($wares) {
            $filteredWares = $wares->where('level', $oVip->level);
            $oVip->setRelation('wares', $filteredWares);
            return $oVip;
        });


        $data = [
            'all_privileges' => $vipPrivileges,
            'o_vips' => $oVips,
        ];
        return $data;
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
            if ($user->id == $from->id)  throw new \Exception(__("api_responses.notSend"));
            if ($sender->di < $total) throw new \Exception('balance low');
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
        $user = $this->userRepository->searchUser($request->user_id);
        if (!$user) throw new \Exception('api_responses.notFound');
        if ($user->id == $from->id)  throw new \Exception(__("api_responses.notSend"));
        $data = [
            'sender_id' => $from->id,
            'user_id' => $user->id,
        ];
        $this->userVipRepository->update($data, $user_vip->id);
        return $user_vip;
    }

    public function createWareVip($request)
    {
        $vipPrivilege  = $this->vipPrivilegeRepository->findById($request->vipPrivilege_id);
        $Vip = $this->ovipRepository->findById($request->ovip_id);

        if ($request->hasFile('image')) {
            $image = Common::upload('images', $request->file('image'));
        }
        if ($request->hasFile('img2')) {
            $img2 = Common::upload('images', $request->file('image'));
        }
        $data = [
            'get_type' => 1,
            'type' => $vipPrivilege->type,
            'price' => 0,
            'name' => $request->name,
            'name_en' => $request->name_en,
            'title' => $request->title,
            'title_en' => $request->title_en,
            'level' => $Vip->level,
            'show_img' => $image,
            'img2' => $img2,
            'image_type' => $request->img2_type,
            'enable' => 1,
            'is_active_for_vip' => 1,

        ];

        $ware = $this->wareRepository->findById($request->ware_id);
        if (!$ware) {
            $this->wareRepository->create($data);
        } else {
            $this->wareRepository->update($data, $ware->id);
        }

        return true;
    }

    public function wareVip($request)
    {
        $vipPrivilege  = $this->vipPrivilegeRepository->findById($request->vipPrivilege_id);
        $Vip = $this->ovipRepository->findById($request->ovip_id);
        return $this->wareRepository->getByTypeAndLevel($vipPrivilege->type, $Vip->level);
    }

    public function badges($type)
    {
        return $this->vipRepository->badgesVip($type);
    }

    public function deleteWare($wareId)
    {
        $ware = $this->wareRepository->findById($wareId);
        $ware->delete();
        return true;
    }


    public function buyVipWithActive($request)
    {
        $vip = $this->ovipRepository->findById($request->vip_id);
        if (!$vip) return Common::apiResponse(0, __('api_responses.not_found'), null, 404);
        $qty = $request->qty ?: 1;
        $total = $vip->price * $qty;
        $expire = $vip->expire;
        if ($expire == 0) {
            $ex = 0;
        } else {
            // $ex = now()->addDays($expire * $qty)->timestamp;
            $ex = now()->diffInDays(now()->addDays($expire * $qty));
        }
        if ($request->type == 1) {
            $type = 1;
            if (!$request->to_user) return Common::apiResponse(0, __('api_responses.missing_params'), null, 422);
            $user_id = $request->to_user;
            $user = $this->userRepository->searchUser($user_id);
            if (!$user) return Common::apiResponse(0, __('api_responses.not_found'), null, 404);
            $user_id = $user->id;
            $sender = $request->user();
            $sender_id = $sender->id;
            if ($sender->di < $total) return Common::apiResponse(0, __('api_responses.low_balance'), null, 407);
            $from = $sender;
        } else {
            $type = 0;
            $user = $request->user();
            $user_id = $user->id;
            $sender_id = 0;
            if ($user->di < $total) return Common::apiResponse(0, __('api_responses.low_balance'), null, 407);
            $from = $user;
        }

        DB::beginTransaction();
        try {
            $from->decrement('di', $total);
            $this->userVipRepository->deleteByLevel($user_id, $vip->level);
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
                'is_used' => 1,
            ];

            $data = $this->userVipRepository->create($data);
            Log::info(json_decode($data));
            Common::handelVip($vip, $user);
            DB::commit();
            CustomNotification::vips($user, $ex, $vip->img);


            return Common::apiResponse(1, 'done', null, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
