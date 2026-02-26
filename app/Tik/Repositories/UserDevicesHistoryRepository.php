<?php

namespace App\Tik\Repositories;

use App\Support\PackageHelper;
use Utd\SwitchAccount\Entities\UserDevicesHistory;


class UserDevicesHistoryRepository extends AbstractRepository
{

    public function __construct()
    {
        if (PackageHelper::isInstalled('switchAccount')) {
            parent::__construct(new UserDevicesHistory());
        } else {
            parent::__construct(null);
        }
    }

    public function all($perPage, $Page, $deviceToken, $request)
    {
        $search = $request->search;
        return $this->model
        ->when(isset($deviceToken), function ($query) use ($deviceToken) {
            $query->where('device_token', $deviceToken);
        })
        ->when($search, function ($query) use ($search) {
            $query->where(function($q2)use($search){

                $q2->where('id', $search)
                ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('phone', 'LIKE', "%$search%")
                            ->orWhere('name', 'LIKE', "%$search%")
                            ->orWhere('uuid', $search);
                });
            });
        })
        ->paginate($perPage, ['*'], 'page', $Page);
    }


}
