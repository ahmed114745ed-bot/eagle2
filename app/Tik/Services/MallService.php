<?php

namespace App\Tik\Services;

use Exception;
use App\Helpers\Common;
use Illuminate\Support\Facades\DB;
use App\Tik\Repositories\PackRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\WareRepository;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Public\Http\Services\UpgradeLevelServices;

class MallService
{
    public function __construct(
        private readonly PackRepository $packRepository,
        private readonly WareRepository $wareRepository,
        private readonly UserRepository $userRepository,

    ) {}

    public function getWares($userId, $type)
    {
        return $this->wareRepository->all($userId, $type);
    }

    public function buyWares($user, $wareId, $quantity)
    {
        $ware = $this->wareRepository->getById($wareId);

        if (!$ware) return Common::apiResponse(0, 'item not found or not for sale', null, 404);
        $pack        = $this->packRepository->userPack($user->id, $ware->id);

        $totalPrice = $ware->price * $quantity;
        if ($user->di < $totalPrice) return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        if ($pack) {

            $this->updatePack($user, $pack, $ware, $quantity, $totalPrice, 'buy');
        }
        try {

            // no pack so create it
            $data = [
                'user_id'   => $user->id,
                'type'      => $ware->type,
                'get_type'  => $ware->get_type,
                'target_id' => $ware->id,
                'num'       => 1, //$qty,
                'expire'    => $ware->expire ? time() + ($quantity * $ware->expire * 86400) : 0,
                'is_read'   => 1,
                'use_num'   => $ware->num,
                'price'     => $totalPrice,
            ];
            $this->packRepository->create($data);

            $this->service($user, $ware->exp, $totalPrice, 'buy');
            return Common::apiResponse(1, 'success process');
        } catch (Exception $exception) {
            return Common::apiResponse(0, 'an error occurred please try again later!', null, 400);
        }
    }

    public function sendWare($auth, $wareId, $userId, $quantity)
    {

        $toUser = $this->userRepository->findById($userId);
        if (!$toUser) return Common::apiResponse(0, 'receiver user not found', null, 404);

        $ware = $this->wareRepository->getById($wareId);

        if (!$ware) return Common::apiResponse(0, 'item not found or not for sale', null, 404);
        $pack        = $this->packRepository->userPack($toUser->id, $ware->id);

        $totalPrice = $ware->price * $quantity;
        if ($auth->di < $totalPrice) return Common::apiResponse(0, 'Insufficient balance, please go to recharge!', null, 407);
        if ($pack) {
            $this->updatePack($auth, $pack, $ware, $quantity, $totalPrice, 'send');
        }

        DB::beginTransaction();
        try {

            $data = [
                'user_id'   =>  $toUser->id,
                'type'      => $ware->type,
                'get_type'  => $ware->get_type,
                'target_id' => $ware->id,
                'num'       => 1, //$qty,
                'expire'    => $ware->expire ? time() + ($quantity * $ware->expire * 86400) : 0,
                'is_read'   => 1,
                'use_num'   => $ware->num,
                'price'     => $totalPrice,
            ];
            $this->packRepository->create($data);

            $this->service($auth, $ware->exp, $totalPrice, 'send');
            DB::commit();
            return Common::apiResponse(1, 'success process');
        } catch (\Exception $exception) {
            DB::rollBack();
            return Common::apiResponse(0, 'an error occurred please try again later!', null, 400);
        }
    }

    public function updatePack($user, $pack, $ware, $quantity, $totalPrice, $type)
    {
        if ($pack->expire == 0) return Common::apiResponse(0, 'you have this item in your pack no need to buy it', null, 405);
        if ($pack->expire > now()->timestamp) { // expire pack not finished 
            if ($ware->expire != 0) {

                try {

                    $expire = ($quantity * $ware->expire * 86400);
                    $this->packRepository->updatePriceWithExpire($pack, $expire, $totalPrice);
                    $this->service($user, $ware->exp, $totalPrice, $type);
                    return Common::apiResponse(1, 'success process');
                } catch (Exception $exception) {

                    return Common::apiResponse(0, 'fail', null, 400);
                }
            } else {
                return Common::apiResponse(0, 'you have this item in your pack no need to buy it', null, 405);
            }
        } else {
            // id expire pack finished
            $this->packRepository->delete($pack);
        }
    }

    public function service($user, $wareExp, $totalPrice, $type)
    {
        if ($type == 'buy') {
            (new UpgradeLevelServices())->purchaseItem($user, $wareExp);
            (new UserCounterServices)->eventUser($user, 'mybag', 1);
        }

        $this->userRepository->decrementUserCoins($user, $totalPrice);
    }

    public function bestSaleWare()
    {
        return $this->packRepository->bestSale();
    }
}