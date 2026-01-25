<?php

namespace App\Tik\Repositories;

use App\Support\DynamicReals;
use App\Tik\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Model;

class ReelsRepository extends AbstractRepository
{

    public function __construct()
    {
        $realClass = DynamicReals::getRealClass();
        if ($realClass) {
            $model = new $realClass();
            parent::__construct($model);
        } else {
            // Create a null model to prevent errors
            parent::__construct(new class extends Model {
                protected $table = 'reals';
            });
        }
    }

    public function all($perPage, $Page)
    {
        if (!DynamicReals::isAvailable()) {
            return collect()->paginate($perPage);
        }
        $reals = $this->model->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar']);
        return $reals->paginate($perPage, ['*'], 'page', $Page);
    }



    public function showByUser($id){
        $reals = $this->model->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar'])->where('user_id', $id);
        return $reals->paginate(10);
    }
    public function find($id)
    {
        return  $this->model->query()->where('id', $id)->with(['user:id,name,uuid', 'user.profile:id,user_id,avatar'])->first();
    }

    public function search($input)
    {
        $query = $this->model->query();

        $query->whereHas('user', function ($query) use ($input) {
            $query->fitterByUuid(trim($input));
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
