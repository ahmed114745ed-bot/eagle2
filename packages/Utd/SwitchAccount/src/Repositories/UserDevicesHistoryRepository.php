<?php

namespace Utd\SwitchAccount\Repositories;

use App\Contracts\UserDevicesHistoryRepositoryContract;
use Utd\SwitchAccount\Entities\UserDevicesHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserDevicesHistoryRepository implements UserDevicesHistoryRepositoryContract
{
    protected Model $model;

    public function __construct()
    {
        $this->model = new UserDevicesHistory();
    }

    public function all($perPage, $Page, $deviceToken, $request)
    {
        $search = $request->search;
        return $this->model
            ->when(isset($deviceToken), function ($query) use ($deviceToken) {
                $query->where('device_token', $deviceToken);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q2) use ($search) {
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

    public function findOrFail(int $id, array $relations = []): Model|Collection|Builder|array|null
    {
        return $this->model->with($relations)->findOrFail($id);
    }
}
