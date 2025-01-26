<?php

namespace App\Tik\Services;

use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\AgencyRepository;
use App\Tik\Repositories\AdminUsersRepository;

class ReportService
{
    public function __construct(
        private readonly AgencyRepository $agencyRepository,
        private readonly UserRepository $userRepository,
        private readonly AdminUsersRepository $adminUsersRepository,
    ) {}

    public function report($request)
    {
        if ($request->type == 'users') {
            return $this->userRepository->report($request->uuid, $request->agencyId, $request->month, $request->year, $request->per_page, $request->page);
        } elseif ($request->type == 'agencies') {
            return $this->agencyRepository->report($request->id, $request->month, $request->year, $request->per_page, $request->page);
        } elseif ($request->type == 'agencies_manger') {
            return $this->adminUsersRepository->report($request->id, $request->per_page, $request->page);
        }
    }
}
