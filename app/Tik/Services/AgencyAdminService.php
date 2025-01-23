<?php

namespace App\Tik\Services;

use App\Tik\Repositories\AdminRepository;

class AgencyAdminService
{
    public function __construct(
        private readonly AdminRepository $adminRepository,
    ) {}

    public function allAgencyAdmin($id, $perPage, $page)
    {
        return $this->adminRepository->allAgencyAdmins($id, $perPage, $page);
    }

    public function create($request)
    {
        $userModel = config('admin.database.users_model');
        $user = new $userModel();
        // $user->slug = $request->input('slug');
        // $user->name = $request->input('name');
        // $user->desc_en = $request->input('desc_en');
        // $user->desc_ar = $request->input('desc_ar');
        $user->save();

    }
}
