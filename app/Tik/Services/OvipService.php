<?php

namespace App\Tik\Services;

use Exception;
use App\Helpers\Common;
use App\Tik\Repositories\OvipRepository;
use App\Tik\Repositories\WareRepository;
use App\Tik\Repositories\VipPrivilegeRepository;


class OvipService
{
    public function __construct(
        private readonly OvipRepository $ovipRepository,
        private readonly WareRepository $wareRepository,
        private readonly VipPrivilegeRepository $vipPrivilegeRepository,
    ) {}

    public function index()
    {
        return $this->ovipRepository->getBySortLevel();
    }

    public function create($request)
    {
        $image = null;
        if ($request->hasFile('image')) {
            $image = Common::upload('images', $request->file('image'));
        }
        $dataOvip = [
            'name' => $request->name,
            'level' => $request->level,
            'img' => $image?? '',
            'price' => $request->price,
            'expire' => $request->expire,
            'exp' => $request->exp
        ];
        $ovip =  $this->ovipRepository->create($dataOvip);

        $ovip->privilegs()->sync($request->privileges);

        $notActive = $this->wareRepository->notActive($request->level);
        if ($notActive) {
            foreach ($request->privileges as $privilege) {
                $vip = $this->vipPrivilegeRepository->findById($privilege);
                if (isset($vip->type)) {
                    $this->wareRepository->updateActiveWithType($vip->type, $request->level);
                }
            }
        }
        return true;
    }

    public function show($id)
    {
        return $this->ovipRepository->findById($id);
    }

    public function update($request)
    {
        $image = null;
        if ($request->hasFile('image')) {
            $image = Common::upload('images', $request->file('image'));
        }
        $dataOvip = [
            'name' => $request->name,
            'level' => $request->level,
            'img' =>  $image,
            'price' => $request->price,
            'expire' => $request->expire,
            'exp' => $request->exp
        ];
         $this->ovipRepository->update($dataOvip, $request->o_vip_id);
         $ovip = $this->ovipRepository->findById($request->o_vip_id);
        $ovip->privilegs()->sync($request->privileges);

        $notActive = $this->wareRepository->notActive($request->level);
        if ($notActive) {
            foreach ($request->privileges as $privilege) {
                $vip = $this->vipPrivilegeRepository->findById($privilege);
                if (isset($vip->type)) {
                    $this->wareRepository->updateActiveWithType($vip->type, $request->level);
                }
            }
        }
        return true;
    }

    public function allVIP($search)
    {
        return $this->vipPrivilegeRepository->listVip($search);
    }
}
