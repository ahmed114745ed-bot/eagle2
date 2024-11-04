<?php

namespace App\Tik\Services;

use App\Tik\Repositories\ExchangeRepository;
use App\Tik\Repositories\ExchangeLogRepository;


class ExchangeService
{
    public function __construct(
        private  readonly ExchangeRepository $exchangeRepository,
        private readonly ExchangeLogRepository $exchangeLogRepository
    ) {
    }


    public function index($type)
    {
        return $this->exchangeRepository->getByType($type);
    }

    public function create($user, $exchangeId)
    {
        $ex = $this->exchangeRepository->findById($exchangeId);

        if (!$ex) throw new \Exception('not found');
        if ($user->type_user != 0 || $user->agency_id != 0) throw new \Exception('not allowed');
        if ($user->total_diamond_received < $ex->diamonds) throw new \Exception('balance low');


        $data = [
            'user_id' => $user->id,
            'diamonds' => $ex->diamonds,
            'value' => $ex->value,
            'type' => $ex->type,
            'operation_no' => rand(11111111, 99999999),
        ];

        $this->exchangeLogRepository->create($data);
        $user->monthly_diamond_received -= $ex->diamonds;
        $user->total_diamond_received -= $ex->diamonds;
        $user->sub_receiver_num += $ex->diamonds;
        if ($user->monthly_diamond_received <= 0) {
            $user->monthly_diamond_received = 0;
        }
        if ($ex->type == 0) {
            $user->di += $ex->value;
        } elseif ($ex->type == 1) {
            $user->gold +=  $ex->value;
        }
        $user->save();
        return true;
    }

    public function getExchangeLog($userId, $type)
    {
        return $this->exchangeLogRepository->getExchanges($userId, $type);
    }
}
