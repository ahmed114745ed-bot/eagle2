<?php

namespace App\Tik\Services;


class PaymentGatewayService
{
    public function __construct(
        private readonly PaymentGatewayRepository $paymentGatewayRepository,
    ) {
    }

    public function index()
    {
        return $this->paymentGatewayRepository->all();
    }
}