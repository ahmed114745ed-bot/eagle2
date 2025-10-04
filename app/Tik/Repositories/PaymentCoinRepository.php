<?php

namespace App\Tik\Repositories;

use App\Models\PaymentCoin;

class PaymentCoinRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new PaymentCoin());
    }

    public function index($type = 'user')
    {
        return $this->model
            ->with(['coins' => function ($q) {
                $q->select('*');
            }])
            ->where('package_type', $type)
            ->where('status', true)
            ->get();
    }


    public function findById($paymentCoinId)
    {
        return $this->model->with('coins')->find($paymentCoinId);
    }
}
