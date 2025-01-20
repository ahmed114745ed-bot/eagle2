<?php

namespace App\Tik\Repositories;

use App\Models\Interest;

class InterestRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new Interest());
    }

    public function all($id, $perPage, $page)
    {
        return $this->model->when(isset($id), function ($query) use ($id) {
            $query->where('id', $id);
        })->paginate($perPage, ['*'], 'page', $page);
    }
}
