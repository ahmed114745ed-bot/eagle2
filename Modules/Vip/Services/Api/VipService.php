<?php

namespace Modules\Vip\Services\Api;

use Exception;
use App\Helpers\UserCoinLogHelper;
use Carbon\Carbon;
use App\Helpers\Common;
use Modules\Vip\Helpers\VipCommon;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;
use Modules\Vip\Traits\HandlesApiExceptions;
use App\Tik\Repositories\{
    PackRepository,
    UserRepository,
    WareRepository,
};
use Modules\Vip\Repositories\{
    OvipRepository,
    VipRepository,
    VipPrivilegeRepository
    ,UserVipRepository
};

class VipService
{
    use HandlesApiExceptions;

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
        $privileges = $this->vipPrivilegeRepository->all();
        $oVips = $this->ovipRepository->getBySortLevel();

        $wares = $this->wareRepository->getOVip($oVips->pluck('level'), $privileges->pluck('type'));

        $oVips = $oVips->map(fn($vip) => $vip->setRelation('wares', $wares->where('level', $vip->level)));

        return [
            'all_privileges' => $privileges,
            'o_vips'         => $oVips,
        ];
    }

    public function buyVip($request)
    {
        $vip = $this->ovipRepository->findById($request->vip_id);
        if (!$vip) throw new Exception(__('api_responses.not_found'));

        $qty = $request->qty ?: 1;
        $total = $vip->price * $qty;
        $user = $request->user();

        if ($request->type == 1) {
            if (!$request->to_user) throw new Exception(__('api_responses.missing_params'));
            $targetUser = $this->userRepository->searchUser($request->to_user);
            if (!$targetUser || empty($targetUser->phone)) throw new Exception(__('api.phone'));

            if ($targetUser->id == $user->id) throw new Exception(__('api_responses.notSend'));
            if ($user->di < $total) throw new Exception(__('api_responses.balance_low'));

            $user_id = $targetUser->id;
            $sender_id = $user->id;
            $from = $user;
        } else {
            if ($user->di < $total) throw new Exception(__('api_responses.balance_low'));
            $user_id = $user->id;
            $sender_id = 0;
            $from = $user;
        }
        $amountBefore =  Common::getCurrentBalance($from->id);
        $logAmount = -abs($total);
        UserCoinLogHelper::log(
            $user->id ,
            'vip',
            'o_vips',
            $logAmount ?? 0,
            $amountBefore ?? 0,
            $vip->name
        );
        $this->userRepository->decrementUserCoins($from, $total);
        $this->packRepository->deleteExpirePack();
        $this->packRepository->unUseOldPack($user_id);

        $this->userVipRepository->create([
            'type'      => $request->type ?? 0,
            'sender_id' => $sender_id,
            'user_id'   => $user_id,
            'vip_id'    => $vip->id,
            'level'     => $vip->level,
            'expire'    => null,
            'qty'       => $qty,
            'price'     => $vip->price,
            'total'     => $total,
            'is_used'   => 0,
            'days'      => $vip->expire,
        ]);

        $countWares = $this->wareRepository->countWareByLevel($vip->level);

        return [$user, $countWares, $request->user(), $vip->price];
    }

    public function userVip($request)
    {
        $userVip = $this->userVipRepository->findByIdWithOVip($request->vip_id);
        if (!$userVip) throw new Exception(__('api_responses.vip_not_found'));

        $user = $request->user();
        $isUsed = (bool) $request->type;

        if ($isUsed) {
           
            VipCommon::handleVipActivation($userVip);
        } else {
            VipCommon::deactivateVip($userVip->id, $user->id);
        }

        return ['target_id' => $userVip->id];
    }

    public function usePack($request)
    {
        return $this->userVipRepository->togglePackUsage(
            $request->pack_id,
            $request->user()->id,
            (bool) $request->type
        );
    }

    public function sendVip($request)
    {
        $from = $request->user();
        $userVip = $this->userVipRepository->findById($request->vip_id);

        if (
            !$userVip || $userVip->user_id != $from->id ||
            $userVip->is_used || $userVip->num_used >= 1 || $userVip->using
        ) {
            throw new Exception(__('api_responses.vip_already_used'));
        }

        $toUser = $this->userRepository->searchUser($request->user_id);
        if (!$toUser) throw new Exception(__('api_responses.notFound'));
        if ($toUser->id == $from->id) throw new Exception(__('api_responses.notSend'));

        $userVip->update([
            'sender_id' => $from->id,
            'user_id'   => $toUser->id,
        ]);

        return $userVip;
    }

    public function createWareVip($request)
    {
        $vipPrivilege = $this->vipPrivilegeRepository->findById($request->vipPrivilege_id);
        $vip = $this->ovipRepository->findById($request->ovip_id);
    
        $image = $request->hasFile('image') ? Common::upload('images', $request->file('image')) : null;
        $img2  = $request->hasFile('img2')  ? Common::upload('images', $request->file('img2'))  : null;
    
        $data = [
            'get_type'            => 1,
            'type'                => $vipPrivilege->type,
            'price'               => 0,
            'name'                => $request->name,
            'name_en'             => $request->name_en,
            'title'               => $request->title,
            'title_en'            => $request->title_en,
            'level'               => $vip->level,
            'show_img'            => $image,
            'img2'                => $img2,
            'image_type'          => $request->img2_type,
            'enable'              => 1,
            'is_active_for_vip'   => 1,
        ];
    
        $ware = $this->wareRepository->findById($request->ware_id);
    
        $ware ? $this->wareRepository->update($data, $ware->id)
              : $this->wareRepository->create($data);
    
        return true;
    }

    public function wareVip($request)
    {
        $vipPrivilege = $this->vipPrivilegeRepository->findById($request->vipPrivilege_id);
        $vip = $this->ovipRepository->findById($request->ovip_id);

        return $this->wareRepository->getByTypeAndLevel($vipPrivilege->type, $vip->level);
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
        if (!$vip) {
            return Common::apiResponse(false, __('api_responses.not_found'), null, 404);
        }

        $qty = $request->qty ?: 1;
        $total = $vip->price * $qty;
        $expire = $vip->expire;
        $ex = $expire == 0 ? 0 : now()->addDays($expire * $qty)->timestamp;

        return $this->wrap(function () use ($request, $vip, $qty, $total, $expire, $ex) {
            [$user_id, $from, $type, $sender_id, $user] = $request->type == 1
                ? $this->authUserSend($request, $total)
                : $this->authUser($request, $total);

            DB::beginTransaction();

            try {
                $from->decrement('di', $total);
                $this->packRepository->deleteExpirePack();

                $userVip = $this->userVipRepository->findByUserLevel($user_id, $vip->level, $vip->id);

                if ($userVip) {
                    $newExpire = $userVip->expire == 0 ? 0 : $userVip->expire + ($expire * $qty * 86400);
                    $updateData = [
                        'expire'  => $newExpire,
                        'qty'     => $userVip->qty + $qty,
                        'total'   => $userVip->total + $total,
                        'is_used' => 1,
                    ];
                    $this->userVipRepository->update($updateData, $userVip->id);
                } else {
                    $userVip = $this->userVipRepository->create([
                        'type'      => $type,
                        'sender_id' => $sender_id,
                        'user_id'   => $user_id,
                        'vip_id'    => $vip->id,
                        'level'     => $vip->level,
                        'expire'    => $ex,
                        'qty'       => $qty,
                        'price'     => $vip->price,
                        'total'     => $total,
                        'is_used'   => 1,
                    ]);
                }

                // VipCommon::handelVip($vip, $user, null, $userVip);
                VipCommon::handleVipActivation($userVip);
                
                DB::commit();

                $remainingDays = $ex ? Carbon::parse($ex)->diffInDays(now()) : 0;
                CustomNotification::vips($user, $remainingDays, $vip->img);

                return Common::apiResponse(true, 'done', null, 201);
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e; 
            }
        });
    }

    public function buyVips($request)
    {
        return $this->wrap(function () use ($request) {
            $vip = $this->ovipRepository->findById($request->vip_id);
            if (!$vip) {
                return Common::apiResponse(false, __('api_responses.not_found'), null, 404);
            }

            $qty = $request->qty ?: 1;
            $total = $vip->price * $qty;
            $expire = $vip->expire;
            $ex = $expire === 0 ? 0 : now()->addDays($expire * $qty)->timestamp;

            [$user_id, $from, $type, $sender_id, $user] = $request->type == 1
                ? $this->authUserSend($request, $total)
                : $this->authUser($request, $total);

            DB::beginTransaction();

            try {
                $from->decrement('di', $total);
                $this->packRepository->deleteExpirePack();

                $data = [
                    'type'      => $type,
                    'sender_id' => $sender_id,
                    'user_id'   => $user_id,
                    'vip_id'    => $vip->id,
                    'level'     => $vip->level,
                    'expire'    => $ex,
                    'qty'       => $qty,
                    'price'     => $vip->price,
                    'total'     => $total,
                    'is_used'   => 0,
                ];

                $vipRecord = $this->userVipRepository->create($data);

                // VipCommon::handelVip($vip, $user, null, $vipRecord);

                DB::commit();

                $daysRemaining = $ex ? Carbon::parse($ex)->diffInDays(now()) : 0;
                CustomNotification::vips($user, $daysRemaining, $vip->img);

                return Common::apiResponse(true, 'done', null, 201);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    public function authUserSend($request, $total)
    {
        $type = 1;

        if (!$request->to_user) {
            throw new Exception(__('api_responses.missing_params'));
        }

        $user = $this->userRepository->searchUser($request->to_user);
        if (!$user) {
            throw new Exception(__('api_responses.not_found'));
        }

        $sender = $request->user();
        if ($sender->di < $total) {
            throw new Exception(__('api_responses.low_balance'));
        }

        return [$user->id, $sender, $type, $sender->id, $user];
    }

    public function authUser($request, $total)
    {
        $type = 0;
        $user = $request->user();

        if ($user->di < $total) {
            throw new Exception(__('api_responses.low_balance'));
        }

        return [$user->id, $user, $type, 0, $user];
    }

    public function vipUserList($userId): array
    {
        $userVips = $this->userVipRepository->getAllByUserId($userId);
        $vipPrivileges = $this->vipPrivilegeRepository->all();
    
        $oVips = $userVips->pluck('OVip')->filter();
        $levels = $oVips->pluck('level')->unique();
        $types = $vipPrivileges->pluck('type')->unique();
    
        $wares = $this->wareRepository->getOVip($levels, $types);
    
        $userVips->each(function ($userVip) use ($wares) {
            if ($oVip = $userVip->OVip) {
                $filteredWares = $wares->where('level', $oVip->level);
                $oVip->setRelation('wares', $filteredWares);
            }
        });
    
        return [
            'all_privileges' => $vipPrivileges,
            'o_vips'         => $userVips,
        ];
    }

    public function getLevelGroups(): array
    {
        return $this->vipRepository->getLevelGroups();
    }
}
