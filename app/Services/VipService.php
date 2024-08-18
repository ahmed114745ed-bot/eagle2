<?php

namespace App\Services;

use App\Helpers\Common;
use App\Repositories\VipPrivilegeRepository;
use App\Repositories\OVipRepository;
use App\Repositories\WareRepository;
use App\Http\Resources\WareResource;
use App\Models\User;
use App\Models\Ware;
use App\Repositories\PackRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserVipRepository;
use DB;
use Modules\Public\Http\Services\UpgradeLevelServices;
use Modules\Public\Http\Services\UserCounterServices;

class VipService
{
    protected $vipPrivilegeRepo;
    protected $oVipRepo;
    protected $wareRepo;
    protected $packRepo;
    protected $userVipRepo;
    protected $userRepo;

    public function __construct(
        VipPrivilegeRepository $vipPrivilegeRepo,
        OVipRepository $oVipRepo,
        WareRepository $wareRepo,
        UserRepository $userRepo,
        UserVipRepository $userVipRepo,
        PackRepository $packRepo
    ) {
        $this->vipPrivilegeRepo = $vipPrivilegeRepo;
        $this->oVipRepo     = $oVipRepo;
        $this->wareRepo     = $wareRepo;
        $this->packRepo     = $packRepo;
        $this->userRepo     = $userRepo;
        $this->userVipRepo  = $userVipRepo;
    }

    public function getVipList()
    {
        $vipPrivileges = $this->vipPrivilegeRepo->getAllPrivileges();
        $oVips = $this->oVipRepo->getAllWithPrivileges();
        $wares = $this->wareRepo->getOVip($oVips->pluck('level'), $vipPrivileges->pluck('type'));

        $list = [];
        foreach ($oVips as $i) {
            $privs = [];
            $mp = $i->privilegs->pluck('id')->toArray();
            foreach ($vipPrivileges as $p1) {
                $p = clone $p1;
                $p->name = app()->getLocale() == 'en' ? ($p->en_name ?? $p->name) : $p->name;
                $p->active = in_array($p->id, $mp);
                
                $ware = $wares->where('level', $i->level)->where('type', $p->type)->first() ?? $wares->where('level', 8)->where('type', $p->type)->first();
                
                if ($ware) {
                    $p->item = new WareResource($ware);
                } else {
                    $p->item = new \stdClass();
                }
                
                $privs[] = $p;
            }

            array_multisort(array_column($privs, 'active'), SORT_DESC, $privs);
            unset($i->privilegs);
            $i->privilegs = $privs;

            $list[] = $i;
        }

        return $list;
    }

    public function buyVip($request)
    {
        if (!$request->vip_id) return Common::apiResponse(0, 'missing param', null, 422);

        $vip = $this->oVipRepo->findVipById($request->vip_id);
        if (!$vip) return Common::apiResponse(0, 'not found', null, 404);

        $qty = $request->qty ?: 1;
        $total = $vip->price * $qty;
        $expire = $vip->expire;
        $ex = $expire == 0 ? 0 : now()->addDays($expire * $qty)->timestamp;

        $type = $request->type == 1 ? 1 : 0;
        $user = $type == 1 ? $this->userRepo->findUserByUuid($request->to_user) : $request->user();

        if (!$user) return Common::apiResponse(0, 'not found', null, 404);
        if ($type == 0 && $user->di < $total) return Common::apiResponse(0, 'balance low', null, 407);

        DB::beginTransaction();
        try {
            if ($type == 0) {
                $this->userRepo->decrementBalance($user, $total);
            }

            $this->packRepo->deleteExpiredPacks($user->id);

            $this->userVipRepo->createUserVip([
                'type' => $type,
                'sender_id' => $type == 1 ? $request->user()->id : 0,
                'user_id' => $user->id,
                'vip_id' => $vip->id,
                'level' => $vip->level,
                'expire' => $ex,
                'qty' => $qty,
                'price' => $vip->price,
                'total' => $total,
                'is_used' => 0
            ]);
            $user = $this->userRepo->findUserById($request->user()->id);
            (new UpgradeLevelServices())->buyAristocracy($user, $vip->exp);
            $countWares = Ware::where('get_type', 1)->where('enable', 1)->where('level', $vip->level)->where('is_active_for_vip', 1)->count();
            (new UserCounterServices)->eventUser($user, 'mybag', $countWares);

            DB::commit();
            return Common::apiResponse(1, 'done', null, 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function useVip($request)
    {
        if (!$request->vip_id) {
            return Common::apiResponse(false, __("api_responses.missing_params"), null, 422);
        }

        $userVip = $this->userVipRepo->findUserVipWithOVip($request->vip_id);
        if (!$userVip) {
            return Common::apiResponse(false, __("api_responses.vip_not_found"), null, 422);
        }

        $user = $request->user();
        $isUsed = (bool) $request->type;

        if ($isUsed) {
            $this->userVipRepo->updateUserVipIsUsed($user->id, 0);
        }

        $userVip->is_used = $isUsed;
        $userVip->num_used += 1;
        $this->userVipRepo->saveUserVip($userVip);

        $vip = $userVip->OVip;
        if ($userVip->num_used <= 1) {
            Common::handelVip($vip, $user);
        }

        $data['target_id'] = $userVip->id;
        return Common::apiResponse(1, 'success', $data);
    }

    public function sendVip($request)
    {
        if (!$request->user_id || !$request->vip_id) {
            return Common::apiResponse(false, __("api_responses.missing_params"), null, 422);
        }

        $from = $request->user();
        $userVip = $this->userVipRepo->findUserVipById($request->vip_id);

        if (!$userVip || $userVip->user_id != $from->id) {
            return Common::apiResponse(false, __("api_responses.vip_not_found"), null, 422);
        }

        if ($userVip->is_used == 1 || $userVip->num_used >= 1) {
            return Common::apiResponse(false, 'الـ VIP مستخدم من قبل ولا يمكن إهداؤه', null, 422);
        }

        $userVip->sender_id = $from->id;
        $userVip->user_id = $request->user_id;

        $this->userVipRepo->saveUserVip($userVip);

        return Common::apiResponse(1, 'success', $userVip);
    }
}
