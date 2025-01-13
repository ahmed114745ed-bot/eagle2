<?php

namespace App\Tik\Repositories;

use App\Models\FamilyLevel;
use App\Tik\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;

class FamilyLevelRepository extends AbstractRepository
{

    public function __construct()
    {
        $model = new FamilyLevel;
        parent::__construct($model);

        if (!$this->model instanceof FamilyLevel) return;
    }

    public function all()
    {
        $FamilyLevels = $this->model;
     
        return $FamilyLevels->paginate(10);
    }


    public function find($id)
    {
      return  $this->model->query()->find($id);
    }

    public function delete($family)
    {
        $family->delete();
        return true;
    }
}
