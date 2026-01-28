<?php

namespace Utd\Room\Repositories;

use Modules\Vip\Entities\Vip;
use Utd\Room\Repositories\AbstractRepository;

class RoomVipsRepository extends AbstractRepository
{
    public function __construct(Vip $model)
    {
        parent::__construct($model);
    }

    public function index($perPage = 15, $page = 1)
    {
        return $this->model->paginate($perPage, ['*'], 'page', $page);
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function search($key)
    {
        return $this->model->where('name', 'like', "%{$key}%")
            ->orWhere('description', 'like', "%{$key}%")
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
}
