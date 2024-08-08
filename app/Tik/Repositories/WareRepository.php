<?php

namespace App\Tik\Repositories;

use App\Models\Ware;



class WareRepository extends AbstractRepository
{



    public function __construct()
    {
        parent::__construct(new Ware());
    }


    public function getDressWare($id)
    {
        return $this->model->where(['id' => $id])->first();
    }

    public function getWithType($type,$level)
    {
        return $this->model->query()->where('get_type', 1)
        ->where('type', $type)
        ->select('id', 'img2')
        ->where('level', $level)
        ->first();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function checkWare($type)
    {
        return $this->model->where('type', $type)->exists();
    }
}