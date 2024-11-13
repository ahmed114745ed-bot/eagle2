<?php

namespace App\Tik\Repositories;

use App\Models\PaymentCoin;

class PaymentCoinRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new PaymentCoin());
    }

    public function index()
    {
        return $this->model->with('coins')->get();
    }
}
