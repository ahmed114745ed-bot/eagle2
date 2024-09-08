<?php

namespace App\Tik\Services;


use App\Tik\Repositories\GiftRepository;

class GiftService
{

    public function __construct(
        private readonly GiftRepository $giftRepository,
    ) {}

    public function index($request)
    {
        return $this->giftRepository->all($request);
    }
}
