<?php

namespace App\Tik\Services;

use App\Tik\Repositories\UserRepository;
use App\Http\Resources\UserReportResource;
use App\Tik\Repositories\AgencyRepository;
use App\Http\Resources\AgencyReportResource;
use App\Tik\Repositories\AdminUsersRepository;
use App\Http\Resources\AdminUserReportResource;

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
            $data = $this->userRepository->report($request->uuid, $request->agency_id, $request->month, $request->year, $request->per_page, $request->page);
            return UserReportResource::collection($data);
        } elseif ($request->type == 'agencies') {
            $data = $this->agencyRepository->report($request->id, $request->month , $request->year, $request->per_page, $request->page);
            
            return AgencyReportResource::collection($data);
        } elseif ($request->type == 'agencies_manger') {
            $data = $this->adminUsersRepository->report($request->id, $request->per_page, $request->page);
            return AdminUserReportResource::Collection($data);
        }
    }
}
