<?php

namespace Utd\Reals\Services;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModelService
{
    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    final public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }
}
