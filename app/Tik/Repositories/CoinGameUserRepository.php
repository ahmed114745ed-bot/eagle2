<?php

namespace App\Tik\Repositories;

use App\Models\CoinGameUser;
use Illuminate\Support\Facades\DB;

class CoinGameUserRepository extends AbstractRepository
{


    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct(new CoinGameUser());
    }


    public function topThree()
    {
        return $this->model->select(
            'user_id',
            DB::raw(" SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) AS exp")
        )->groupBy('user_id')->with('user')->orderByRaw("exp desc")->limit(3)->get();
    }
}
