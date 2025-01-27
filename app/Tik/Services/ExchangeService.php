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
        if ($user->type_user != 0 ) throw new \Exception('not allowed');
        if ($user->exchange_diamonds < $ex->diamonds) throw new \Exception('balance low');


        $data = [
            'user_id' => $user->id,
            'diamonds' => $ex->diamonds,
            'value' => $ex->value,
            'type' => $ex->type,
            'operation_no' => rand(11111111, 99999999),
        ];

        $this->exchangeLogRepository->create($data);
        $user->exchange_diamonds -= $ex->diamonds;

        if ($user->exchange_diamonds <= 0) {
            $user->exchange_diamonds = 0;
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

    public function all($id, $perPage, $page)
    {
        return $this->exchangeRepository->all($id, $perPage, $page);
    }

    public function createDashboard($request)
    {

        $this->exchangeRepository->create($request->all());
        return true;
    }

    public function update($id, $request)
    {

        $this->exchangeRepository->update($request->all(), $id);
        return true;
    }

    public function delete($id)
    {

        $data = $this->exchangeRepository->findOrFail($id);
        $data->delete();
        return true;
    }

    public function show($id)
    {
        return $this->exchangeRepository->findOrFail($id);
    }


}
