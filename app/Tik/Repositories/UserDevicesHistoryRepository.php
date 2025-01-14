<?php

namespace App\Tik\Repositories;

use Modules\SwitchAccount\Entities\UserDevicesHistory;


class UserDevicesHistoryRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new UserDevicesHistory());
    }

    public function all($perPage, $Page, $deviceToken)
    {
        return $this->model->whereHas('user')->when(isset($deviceToken), function ($query) use ($deviceToken) {
            $query->where('device_token', $deviceToken);
        })->paginate($perPage, ['*'], 'page', $Page);
    }


}
