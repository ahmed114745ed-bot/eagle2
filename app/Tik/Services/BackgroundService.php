<?php

namespace App\Tik\Services;

use Utd\Room\Repositories\BackgroundRepository;

class BackgroundService
{
    public function __construct(
        private readonly BackgroundRepository $backgroundRepository,
    ) {
    }


    public function index()
    {
        return $this->backgroundRepository->index();
    }
}
