<?php

namespace App\Tik\Repositories;

use App\Models\Music;

class MusicRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new Music());
    }


    public function all()
    {
        return $this->model->with('user')->get();
    }

    public function getByUser($userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }
}
