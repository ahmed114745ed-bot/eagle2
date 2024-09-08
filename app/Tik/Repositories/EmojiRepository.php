<?php

namespace App\Tik\Repositories;

use App\Models\Country;
use App\Models\Emoji;

class EmojiRepository extends AbstractRepository
{


    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new Emoji());
    }

    public function all($request)
    {
        $query = $this->model->query()->where('enable', 1);
        if ($request->pid) {
            $query->where('pid', $request->pid);
        }
        return $query->select('id', 'pid', 'name', 'emoji', 't_length', 'sort', 'name_en')->orderBy('sort')->get();
    }

    public function findById($id)
    {
        return $this->model->query()->select('id', 'pid', 'name', 'emoji', 't_length', 'sort')->find($id);
    }
}
