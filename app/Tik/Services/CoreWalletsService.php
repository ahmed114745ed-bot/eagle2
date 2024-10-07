<?php

namespace App\Tik\Services;

use App\Helpers\Common;
use App\Tik\Repositories\CoreWalletsRepository;



class CoreWalletsService
{
    public function __construct(
        private readonly CoreWalletsRepository $coreWalletsRepository,
    ) {}

    public function index()
    {
        return $this->coreWalletsRepository->all();
    }

    public function create($request)
    {
        $data = [
            'name'         => $request->name,
            'coins'         => $request->coins,
        ];

        $this->coreWalletsRepository->create($data);
        return true;
    }

    public function show($id)
    {
        return $this->coreWalletsRepository->findById($id);
    }

    public function update($request)
    {

        $data = [
            'name'         => $request->name,
            'coins'         => $request->coins,
        ];

        $this->coreWalletsRepository->update($data, $request->Core_wallet_id);
        return true;
    }
}
