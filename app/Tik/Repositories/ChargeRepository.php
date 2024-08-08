<?php

namespace App\Tik\Repositories;


use App\Models\Charge;



class ChargeRepository extends AbstractRepository
{

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Charge());
    }

    // public function create($data)
    // {
    //     return   $this->model->query()->create([
    //         'charger_id' => $data['userId'],
    //         'charger_type' => $data['chargeType'],
    //         'user_id' => $data['receiverId'],
    //         'user_type' => $data['type'],
    //         'amount' => $data['amount'],
    //         'amount_type' => $data['amountType'],
    //         'is_used_transferred' => $data['isTransferred'] ?? false,
    //         'usd' => $data['amount'],
    //         'balance_before' => $data['balance_before'],
    //     ]);
    // }


    public function getChargeHistory($type)
    {
        return $this->model->query()->with([
            'sender'      => function ($query) {
                $query->withoutAppends();
            }, 'receiver' => function ($query) {
                $query->withoutAppends();
            }
        ])->where('charger_type', $type);
    }
}
