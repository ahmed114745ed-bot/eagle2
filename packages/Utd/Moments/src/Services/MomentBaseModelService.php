<?php

namespace Utd\Moments\Services;

use Illuminate\Database\Eloquent\Model;

abstract class MomentBaseModelService
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
