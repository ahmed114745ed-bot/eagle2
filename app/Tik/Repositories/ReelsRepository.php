<?php

namespace App\Tik\Repositories;

use App\Tik\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Modules\Reals\Entities\Real;

class ReelsRepository extends AbstractRepository
{

    public function __construct()
    {
        $model = new Real();
        parent::__construct($model);

        if (!$this->model instanceof Real) return;
    }

    public function all($perPage, $Page)
    {
        $reals = $this->model->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar']);
        return $reals->paginate($perPage, ['*'], 'page', $Page);
    }




    public function find($id)
    {
        return  $this->model->query()->where('id', $id)->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar'])->first();
    }

    public function search($input)
    {
        $query = $this->model->query();

        $query->whereHas('user', function ($query) use ($input) {
            $query->where('uuid', 'like', '%' . trim($input) . '%');
        })->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar']);

        $result = $query->get();

        return $result;
    }

    public function delete($reel)
    {
        $reel->delete();
        return true;
    }
}
